<?php
/**
 * Timer Class
 *
 * Timer-Class
 *
 * Table-Layout:
 * CREATE TABLE `timers` (
 *	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 *	`name` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
 *	`completiontime` DATE NOT NULL ,
 *	UNIQUE (
 *	`name`
 *	)
 *	) ENGINE = MYISAM ;
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: timer.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Controller;
use DateTime;

/**
 * Timer Class
 *
 * Timer-Class
 *
 * Example for a <b>private</b> timer:
 * <code>
 * // Take a Walk of exactly 1 hour
 * $timer = new Timer("walking", Character);
 * $timer->set(0, 0, 1, false);
 * if ($timer->get()) {
 *     $page->output("I'm still walking");
 * } else {
 *     $page->output("Yes! Finally i'm there!'");
 * }
 * </code>
 * Example for a <b>global</b> timer:
 * <code>
 * // Let it rain for 6 hours
 * $timer = new Timer("rain");
 * $timer->set(0, 0, 6, false);
 * if ($timer->get()) {
 *     $page->output("It's still raining");
 * } else {
 *     $page->output("Weee! It stops raining!!");
 * }
 * </code>
 *
 * @package Ruins
 */
class Timer
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
     * Timer Object
     * @var \Entities\Timer
     */
    private $_timer;

    /**
     * constructor - load the default values and initialize the attributes
     * @param string $timername Name of the Timer we manage
     * @param Character $character Affected Character Object - Defines the timer as private
     */
    function __construct($timername, $character = false)
    {
        // Set Timername
        if ($character) {
            $timername = "_".$character->id."_".$timername;
        } elseif (substr($timername, 0, 1) == "_") {
            throw new \Error("Timers with a Name starting with '_' are not allowed");
        }

        // Load or create the Timer
        if ($this->_timer = $this->_getTimer($timername)) {
            // Make sure a stopped Timer is also stopped after loading
            if (!$this->isRunning()) {
                $this->stop();
            }
        } else {
            $this->create($timername);
        }

    }

    /**
     * Create a new Timer
     */
    public function create($timername)
    {
        global $em;

        $newtimer = new \Entities\Timer;
        $newtimer->name = $timername;
        $em->persist($newtimer);
        $em->flush();

        $this->_timer = $newtimer;
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
            $this->_timer->completiontime = $datetime;
            $this->_timer->backup_ttc = 0;
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
            $this->_timer->completiontime = $datetime;
            $this->_timer->backup_ttc = 0;
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
        $this->_timer->backup_ttc = $this->_getTimeDiff($this->_getTTC());

        $this->_replacenow = true;
        $this->useReplacementText("<span class='timer_stop'>".date("H:i:s", mktime(0, 0, $this->_timer->backup_ttc)) ."</span>");
    }

    /**
     * Check if a Timer is running
     * @return bool true if the timer is running, false if the timer is stopped
     */
    public function isRunning()
    {
        if ($this->_timer->backupttc) {
            return false;
        } else {
            return true;
        }
    }

     /**
     * Get the current ttc or the backup ttc if exists
     * @return DateTime Time to complete
     */
    private function _getTTC()
    {
        // Get timetocomplete from BackupTTC or calculate from completiontime
        if ($seconds = $this->_timer->backup_ttc) {
            return new DateTime("+".$seconds." seconds");
        } else {
            return $this->_timer->completiontime;
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

    /**
     * Return the Timer
     * @param string $name Name of the Timer
     * @return int ID of the Timer or false if non-existing
     */
    private function _getTimer($name)
    {
        $qb = getQueryBuilder();

        $result = $qb   ->select("timer")
                        ->from("Entities\Timer", "timer")
                        ->where("timer.name = ?1")->setParameter(1, $name)
                        ->getQuery()
                        ->getOneOrNullResult();

        return $result;
    }
}
?>
