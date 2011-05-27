<?php
/**
 * Character Class
 *
 * Base Character-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: character.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Character Class
 *
 * Character-Class
 * @package Ruins
 */
abstract class Character extends DBObject
{
    /**
     * Armor/Clothing
     * @var Armorset
     */
    public $armor;

    /**
     * Debuglog
     * @var DebugLog
     */
    public $debuglog;

    /**
     * Character Settings
     * @var CharacterSettings
     */
    public $settings;

    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings = false)
    {
        // Call Constructor of the Parent-Class
        parent::__construct($settings);

        // Initialize Attributes
        $this->armor 	= new Armorset($this);

        // Overloading
        $this->properties_overload['strength'] = true;
    }

    /**
     * Enable Modules after loading
     * @see includes/classes/BaseObject#mod_postload()
     */
    public function mod_postload()
    {
        // Set the Parent for all following Modules
        ModuleSystem::setParent($this);

        // load Rightgroups Module
        ModuleSystem::enableManagerModule($this->rightgroups, "Rights");

        // load Money Module
        ModuleSystem::enableManagerModule($this->money, "Money");

        // Load Race Module
        ModuleSystem::enableRaceModule($this);

        // Unset the Parent again, we don't need it further
        ModuleSystem::unsetParent();

        // Enable DebugLog (needs a loaded Character)
        $this->debuglog = new DebugLog($this);

        // Enable CharacterSettings
        $this->settings = new CharacterSettings($this);
    }

    /**
     * Care about everything needed for login
     */
    public function login()
    {
        if ($this->isloaded) {
            // Set lastpagehit to avoid immediately logout
            $this->lastpagehit = date("Y-m-d H:i:s");
        }
    }

    /**
     * Check if this Character has a connection Timeout
     * @return bool the age of the last pagehit in minutes if timeout occurred, else false
     */
    public function hasConnectionTimeout()
    {
        global $config;

        // return false if lastpagehit is not set
        if (strtotime($this->lastpagehit) == 0) {
            return false;
        }

        // get last pagehit in minutes
        $lastpagehitage = ceil((time() - strtotime($this->lastpagehit)) / 60);

        // default connectiontimeout is 15 Minutes
        if ($this->lastpagehit && $lastpagehitage > $config->get("connectiontimeout", 15) ) {
            // connection timout occurred!
            // return age of last pagehit (in Minutes)
            return $lastpagehitage;
        } else {
            // no connection timeout (pagehit is in time)
            return false;
        }
    }

    /**
     * Check if the Character is currently participating in a Battle
     * @param int $battleid Check for a specific Battleid
     * @return int|bool The BattleID if the character is in a battle, else false
     */
    public function isInABattle($battleid=false)
    {
        $dbqt = new QueryTool();

        $dbqt	->select("battleid")
                ->from("battlemembers")
                ->where("characterid=".$this->id);

        if ($battleid) {
            $dbqt->where("battleid=".$battleid);
        }

        if ($result = $dbqt->exec()->fetchOne()) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Check if the Character has the Battletoken
     * @param int $battleid Check for a specific Battleid
     * @return bool true if the character has the Battletoken, else false
     */
    public function hasBattleToken($battleid=false)
    {
        $dbqt = new QueryTool();

        $dbqt	->select("id")
                ->from("battlemembers")
                ->where("characterid=".$this->id)
                ->where("token=1");

        if ($battleid) {
            $dbqt->where("battleid=".$battleid);
        }

        if ($dbqt->exec()->numRows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the Characters BattleID
     * @return mixed The Battleid if Character is in a battle, else false
     */
    public function getBattleID()
    {
        $dbqt = new QueryTool();

        $result = $dbqt	->select("battleid")
                        ->from("battlemembers")
                        ->where("characterid=".$this->id)
                        ->exec()
                        ->fetchOne();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Check if the Characte made a Battle Action
     * @param int $battleid Battle ID
     * @return bool
     */
    public function madeBattleAction($battleid)
    {
        $dbqt = new QueryTool();

        $result = $dbqt	->select("battleid")
                        ->from("battletable")
                        ->where("initiatorid=".$this->id)
                        ->where("battleid=".$battleid)
                        ->exec()
                        ->numRows();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Calculate the Characters Speed
     * @return int calculated Speed
     */
    public function calculateSpeed()
    {
        $speed = $this->race->getBaseSpeed();
        return $speed;
    }

    /**
     * Overload the Strength-Property
     * @param $parameter
     * @return unknown_type
     */
    protected function _overload_strength($parameter)
    {
        //FIXME: THIS IS JUST AN EXAMPLE!!!
        return $parameter+1;
    }
}
?>
