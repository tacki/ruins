<?php
/**
 * User Class
 *
 * User-Class
 *
 * Table-Layout (example):
 * CREATE TABLE `user` (
 *	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 *	`login` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
 * 	`password` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
 * 	) ENGINE = MYISAM ;
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: user.class.php 326 2011-04-19 20:19:34Z tacki $
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
class User extends DBObject
{

    /**
     * Character-Data
     * @var Character
     */
    public $char;

    /**
     * User-Settings
     * @var object
     */
    public $settings;

    /**
     * Debuglog
     * @var DebugLog
     */
    public $debuglog;

    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings = false)
    {
        // Call Constructor of the Parent-Class
        parent::__construct($settings);
    }

    /**
     * @see includes/classes/BaseObject#mod_postload()
     */
    public function mod_postload()
    {
        // load usersettings
        $this->settings = new UserSettings($this);

        // enable debuglog
        $this->debuglog = new DebugLog($this);
    }

    /**
     * Care about everything needed for login
     */
    public function login()
    {
        if ($this->isloaded) {
            // Set Session User ID
            SessionStore::set('userid', $this->id);

            // Check and update UniqueID-List if needed
            $this->checkUniqueID();

            // Check and update IPAddress-List if needed
            $this->checkIPAddress();

            // set loggedin-flag
            $this->loggedin = 1;
        }
    }

    /**
     * Care about everything needed for logout
     */
    public function logout()
    {
        if ($this->isloaded) {
            // unload Character
            $this->unloadCharacter();

            // unset Session User ID
            SessionStore::remove('userid');

            // prune Cache
            SessionStore::pruneCache();

            // set loggedin-flag
            $this->loggedin = 0;
        }
    }

    /**
     * Load Character
     * @param integer $characterid Character to load (if not $user->current_character)
     */
    public function loadCharacter($characterid = false)
    {
        // this is the default character id
        $id = $this->current_character;

        // overwrite if $characterid is set
        if ($characterid) {
            $id = $characterid;
        }

        // Detect CharacterType
        $classname = Manager\User::getCharacterType($id);

        // Load Character
        if (class_exists($classname)) {
            // Create Characterobject using the CharacterType
            $this->char = new $classname;

            // load the Character
            $this->char->load($id);

            // set loggedin-flag
            $this->char->loggedin = 1;
        } else {
            throw new Error("Invalid Charactertype in Database for id " . $id . "!");
        }
    }

    /**
     * Unload Character
     */
    public function unloadCharacter()
    {
        if ($this->char->isloaded) {
            // Save loggedin Flag
            $this->char->loggedin = 0;
            $this->char->save('loggedin');

            // Unload Previous Char
            $this->char->unload();
        }
    }

    /**
     * Add a Logentry to the Users Log
     * @param string $logentry Entry to add
     */
    public function log($logentry)
    {
        global $config;

        if (!($this->log instanceof ObjectLog)) {
            $this->log = new ObjectLog($config->get("userLogMaxSize", 100));
        }

        if ($this->log instanceof ObjectLog) {
            $this->log->add("[" . date("Y-m-d H:i:s") . "]" . $logentry);
        }
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
}

?>
