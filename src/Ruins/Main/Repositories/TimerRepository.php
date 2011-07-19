<?php
/**
 * Timer Repository
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Main\Repositories;
use DateTime;
use Ruins\Main\Entities\Character;
use Ruins\Main\Entities\Timer;
use Ruins\Common\Controller\Url;
use Ruins\Common\Controller\Error;
use Ruins\Common\Controller\Form;

/**
 * Timer Repository
 * @package Ruins
 */
class TimerRepository extends Repository
{
    /**
    * Set this to replace the Timer with HTML instead of reload the page
    * @var string HTML-Element
    */
    private $_replacement = false;

    /**
     * Replace imediatelly
     * @var bool
     */
    private $_replacenow = false;

    /**
     * Create a new Timer
     * @param string $timername
     * @param Ruins\Main\Entities\Character $character
     * @throws Ruins\Common\Controller\Error
     * @return Ruins\Main\Repositories\TimerRepository
     */
    public function create($timername, Character $character=NULL)
    {
        // Set Timername
        // Private Timers start with '_'
        if ($character) {
            $timername = "_".$character->id."_".$timername;
        } elseif (substr($timername, 0, 1) == "_") {
            throw new Error("Timers with a Name starting with '_' are not allowed");
        }

        if (!($timer = $this->findOneByName($timername))) {
            $timer = new Timer;
            $timer->name = $timername;

            $this->getEntityManager()->persist($timer);
        }

        $this->setEntity($timer);

        if (!$timer->isRunning()) {
            $this->stop();
        }

        return $this;
    }

    /**
     * Set new Date for this Timer
     * @param DateTime $datetime
     */
    public function setNewTime(DateTime $datetime)
    {
        $this->getEntity()->completiontime = $datetime;
        $this->getEntity()->backup_ttc     = 0;
    }

    /**
    * Don't refresh the page if the Timer finished, but replace the Timer with a Button
    * @param string $buttonText Text on the Button
    * @param URL $targetUrl Url the Button sends the User to
    */
    public function useReplacementButton($buttonText, URL $targetUrl)
    {
        $html		= "";
        $tempform 	= new Form;

        $html 		.= $tempform->head("", $targetUrl);
        $html 		.= $tempform->submitButton($buttonText);
        $html 		.= $tempform->close();

        $this->_replacement = $html;
    }

    /**
     * Don't refresh the page if the Timer finished, but replace the Timer with some Text
     * @param string $text Text instead of the Timer
     */
    public function useReplacementText($text)
    {
        $this->_replacement = $text;
    }

    /**
     * Set a Timer
     * @param integer $seconds # of seconds the timer should run
     * @param integer $minutes # of minutes the timer should run
     * @param integer $hours # of hours the timer should run
     * @param bool $force Force to set a new Timer (overwrite existing ones)
     * @return bool true if successful, else false
     */
    public function set($seconds, $minutes=0, $hours=0, $force=false)
    {
        if (!$this->get() || $force) {
            $totaltime 	= $seconds + $minutes*60 + $hours*3600;
            $datetime = new DateTime("+".$totaltime." seconds");

            // set new time or force to overwrite
            $this->setNewTime($datetime);
        } else {
            // timer exists and force isn't enabled -> do nothing
            return false;
        }

        return true;
    }

    /**
     * Set a Timer
     * @param DateTime $datetime Set with DateTime Object
     * @param bool $force Force to set a new Timer (overwrite existing ones)
     * @return bool true if successful, else false
     */
    public function setWithDateTime(DateTime $datetime, $force=false)
    {
        if (!$this->get() || $force) {
            // set new time or force to overwrite
            $this->setNewTime($datetime);
        } else {
            // timer exists and force isn't enabled -> do nothing
            return false;
        }

        return true;
    }

    /**
     * Get a Timer
     * @return string The Timer in HTML if successful, false if the timer is done
     */
    public function get()
    {
        if (!($completiontime = $this->_getTTC())) {
            return false;
        }

        $timediff = $this->_getTimeDiff($completiontime);

        if ($timediff > 0 || $this->_replacenow) {
            $html 	= "";
            $clock = date("H:i:s", mktime(0, 0, $timediff));

            if ($this->_replacenow) {
                $html .= $this->_replacement;
            } elseif ($this->_replacement) {
                $html .= "<span class='timer_replace'>".$clock."</span>";
                $html .= "<span style='display:none'>".$this->_replacement."</span>";
            } else {
                $html .= "<span class='timer'>".$clock."</span>";
            }
            return $html;
        } else {
            return false;
        }
    }

    /**
     * Start the Timer if stopped
     */
    public function start()
    {
        if ($backup_ttc = $this->_getTTC()) {
            $this->setWithDateTime($backup_ttc, true);

            $this->_replacenow = false;
            $this->_replacement = false;
        }
    }

    /**
     * Stop the Timer
     */
    public function stop()
    {
        $this->getEntity()->backup_ttc = $this->_getTimeDiff($this->_getTTC());

        $this->_replacenow = true;
        $this->useReplacementText("<span class='timer_stop'>".date("H:i:s", mktime(0, 0, $this->getEntity()->backup_ttc)) ."</span>");
    }

    /**
     * Check if a Timer is running
     * @return bool true if the timer is running, false if the timer is stopped
     */
    public function isRunning()
    {
        return $this->getEntity()->isRunning();
    }

    /**
     * Get the current ttc or the backup ttc if exists
     * @return DateTime Time to complete
     */
    private function _getTTC()
    {
        // Get timetocomplete from BackupTTC or calculate from completiontime
        if ($seconds = $this->getEntity()->backup_ttc) {
            return new DateTime("+".$seconds." seconds");
        } else {
            return $this->getEntity()->completiontime;
        }
    }

    /**
     * Get Timediff (in seconds) to now()
     * @param DateTime $datetime
     */
    private function _getTimeDiff(DateTime $datetime)
    {
        $now = new DateTime();

        return $datetime->getTimestamp() - $now->getTimestamp();
    }
}