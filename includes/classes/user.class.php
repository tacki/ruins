<?php
/**
 * User Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * User Class
 *
 * User-Class
 * @package Ruins
 */
class User extends EntityHandler
{
    /**
     * constructor - load the default values and initialize the attributes
     */
    function __construct($id)
    {
        $this->loadEntity("Entities\User", $id);
    }

    /**
     * Care about everything needed for login
     */
    public function login()
    {
        // Set Session User ID
        SessionStore::set('userid', $this->id);

        // Check and update UniqueID-List if needed
        $this->checkUniqueID();

        // Check and update IPAddress-List if needed
        $this->checkIPAddress();

        // set loggedin-flag
        $this->loggedin = 1;
    }

    public function checkIPAddress()
    {
        global $config;

        if (!($this->iplist instanceof IPStack)) {
            $this->iplist = new IPStack($config->get("userIPListSize", 10));
        }

        $this->iplist->addIP();
    }

    /**
     * Check the current UniqueID
     */
    public function checkUniqueID()
    {
        global $config;

        if (!($this->uniqueid instanceof UniqueIDStack)) {
            $this->uniqueid = new UniqueIDStack($config->get("userUniqueIDSize", 10));
        }

        if (!isset($_COOKIE['ruins_uniqueid']) && strlen($_COOKIE['ruins_uniqueid']) != 32) { // 32=Size of MD5Hash
            // No Cookie set
            if ($this->uniqueid->getLast("data") !== $_COOKIE['ruins_uniqueid']) {
                // Generate a new Unique ID and add it to the List. Also set
                // a new Cookie
                $this->uniqueid->setNewUniqueID();

                // Add Logentry
                $this->log("New UniqueID generated!");
            } else {
                // Update Cookie with UniqueID from Database
                $_COOKIE['ruins_uniqueid'] = $this->uniqueid->getLast("data");
            }
        } else {
            // Cookie set, update DB?
            if ($this->uniqueid->getLast("data") !== $_COOKIE['ruins_uniqueid']) {
                // Add UniqueID in Cookie to the List
                $this->uniqueid->add($_COOKIE['ruins_uniqueid']);
            }
        }
    }

    /**
     * Check if the Character has a connection Timeout
     * @return bool the age of the last pagehit in minutes if timeout occurred, else false
     */
    public function hasConnectionTimeout()
    {
        global $config;

        // return false if lastpagehit is not set
        if (!isset($this->character->lastpagehit)) {
            return false;
        }

        // get last pagehit in minutes
        $lastpagehit = $this->character->lastpagehit->diff(new DateTime())->format("%i");

        // default connectiontimeout is 15 Minutes
        if ($lastpagehitage > $config->get("connectiontimeout", 15) ) {
            // connection timout occurred!
            // return age of last pagehit (in Minutes)
            return $lastpagehitage;
        } else {
            // no connection timeout (pagehit is in time)
            return false;
        }
    }

    public function addDebugLog($text)
    {
        global $em;

        $logentry = new Entities\DebugLog;
        $logentry->userid = $this->getEntity();
        $logentry->text = $text;
        $em->persist($logentry);
    }

}