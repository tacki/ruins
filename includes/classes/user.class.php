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

        // Add IP Address to the List
        $this->addIPAddress();

        // set loggedin-flag
        $this->loggedin = 1;
    }

    /**
     * Add the IP Address to the List
     */
    public function addIPAddress()
    {
        global $em;

        $lastIP     = $this->iplist->last()->ip;
        $requestIP  = getRequestTrueIP();

        if (is_null($lastIP) || ($lastIP != $requestIP) ) {
            // Add if IP has changed
           $newIP = new Entities\UserIP;
           $newIP->user = $this->getEntity();
           $newIP->ip = $requestIP;
           $em->persist($newIP);
        }
    }

    /**
     * Add a new UniqueID to the List
     */
    public function checkUniqueID()
    {
        global $em;

        $lastID = $this->uniqueidlist->last()->uniqueid;

        if (!isset($_COOKIE['ruins_uniqueid']) || strlen($_COOKIE['ruins_uniqueid']) != 32) { // 32=Size of MD5Hash
            // No Cookie or invalid Cookie is set
            if ($lastID !== $_COOKIE['ruins_uniqueid']) {
                // Generate a new Unique ID and add it to the List. Also set
                // a new Cookie
               $newID = new Entities\UserUniqueID;
               $newID->user = $this->getEntity();
               $newID->uniqueid = generateUniqueID();
               $em->persist($newID);

                // Add Logentry
                $this->addDebugLog("New UniqueID generated!");
            } else {
                // Update Cookie with UniqueID from Database
                // Used for special tracking-ids, shorter than 32 chars
                $_COOKIE['ruins_uniqueid'] = $lastID;
            }
        } elseif ($lastID !== $_COOKIE['ruins_uniqueid']) {
            // A Cookie is set, add to DB because it has changed
            $newID = new Entities\UserUniqueID;
            $newID->user = $this->getEntity();
            $newID->uniqueid = $_COOKIE['ruins_uniqueid'];
            $em->persist($newID);
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