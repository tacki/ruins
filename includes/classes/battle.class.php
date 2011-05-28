<?php
/**
 * Battle Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: battle.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Class defines
 */
define("BATTLE_SIDE_ATTACKERS", "attackers");
define("BATTLE_SIDE_DEFENDERS", "defenders");
define("BATTLE_SIDE_NEUTRALS", "neutrals");
define("BATTLE_MEMBERSTATUS_ACTIVE", 0);
define("BATTLE_MEMBERSTATUS_INACTIVE", 1);
define("BATTLE_MEMBERSTATUS_EXCLUDED", 2);
define("BATTLE_MEMBERSTATUS_BEATEN", 4);

/**
 * Battle Class
 *
 * @package Ruins
 */
class Battle extends DBObject
{
    /**
     * Initialized Flag
     * @var bool
     */
    public $initialized = false;

    /**
     * QueryTool Object
     */
    private $_dbqt;

    /**
     * Battle Timer
     * @var Timer
     */
    private $_timer;

    /**
     * constructor - load the default values and initialize the attributes
     */
    public function __construct()
    {
        parent::__construct();
        $this->_dbqt = new QueryTool();

        // Add the Helper Functions
        // Only if we have an OutputObject
        if (getOutputObject()) {
            $this->_addHelper();
        }
    }

    /**
     * destructor - save the battlestatus
     * @return unknown_type
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Initialize for a new Battle
     */
    public function initialize()
    {
        global $user;

        // Create the Battle Instance
        $this->create();

        // Add the Initiator to the Battle Members
        $this->initiatorid = $user->char->id;

        // Create the Battletick-Timer
        $this->initTimer();

        // Init Round-Number
        $this->round = 1;

        // Save and reload Battle
        $battleid = $this->save();
        $this->load($battleid);

        // Set initialized-Flag
        $this->initialized = true;
    }

    /**
     * Check if a Character is a Member of this Battle
     * @param Character $char Character to check
     * @return bool true if the char is a Member, else false
     */
    public function isMember(Character $char)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        if ($char->isInABattle($this->id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a new Member to the Battle
     * @param Character $char
     * @param string $side The side the Character joins (attacker, defender, ...)
     * @return bool true if successful, else false
     */
    public function addMember(Character $char, $side)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            throw new Error("Cannot add a Member to a Battle if it's not initialized");
        }

        if ($this->isMember($char)) {
            return false;
        }

        // add the Char to the Battlemembers-List
        $this->_dbqt->clear();
        $data = array(
                        "battleid" => $this->id,
                        "characterid" => $char->id,
                        "side" => $side,
                        "speed" => $char->calculateSpeed(),
                        "actiondone" => false,
                        "token" => false );

        $result = $this->_dbqt	->insertinto("battlemembers")
                                ->data($data)
                                ->exec();

        return $result;
    }

    /**
     * Remove a Member from the Battle
     * @param Character $char The Character to remove
     * @return bool true if successful, else false
     */
    public function removeMember(Character $char)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        // Cycle Token if this is the token-owner
        if ($this->getTokenOwner() == $char->id) {
            // Give the Token to another Battle Member
            $this->cycleToken();
        }

        // remove the Char from the Battlemembers-List
        $this->_dbqt->clear();

        $result = $this->_dbqt	->deletefrom("battlemembers")
                                ->where("battleid=".$this->id)
                                ->where("characterid=".$char->id)
                                ->exec();

        // Check if there are enough Battle Members to continue
        if ( !($this->getAttackerList())
            || !($this->getDefenderList()) ) {
            $this->finish();
        }

        return $result;
    }

    /**
     * Completly remove a Battle and all Members/Messages
     */
    public function removeBattle()
    {
        if ($this->initialized) {
            // Remove all Battlemembers
            $this->_dbqt->clear();
            $this->_dbqt->deletefrom("battlemembers")
                        ->where("battleid=".$this->id)
                        ->exec();

            // Remove Battlemessages
            $this->_dbqt->clear();
            $this->_dbqt->deletefrom("battlemessages")
                        ->where("battleid=".$this->id)
                        ->exec();

            // Clear Battletable
            $this->_dbqt->clear();
            $this->_dbqt->deletefrom("battletable")
                        ->where("battleid=".$this->id)
                        ->exec();

            // Remove Timer
            $this->_dbqt->clear();
            $this->_dbqt->deletefrom("timers")
                        ->where("name=".$this->_dbqt->quote($this->battletimer))
                        ->exec();

            // Remove initialized Flag
            $this->initialized = false;

            // Remove Battle
            $this->delete();
        }
    }

    /**
     * Get MemberEntry of the current Battle
     * @param int|Character $char Character ID or Character Object
     * @return mixed Array of Memberdata else false
     */
    public function getMemberEntry($char)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $this->_dbqt->select("*")
                    ->from("battlemembers");

        if ($char instanceof Character) {
            $this->_dbqt->where("characterid=".$char->id);
        } else {
            $this->_dbqt->where("characterid=".$char);
        }

        $this->_dbqt->where("battleid=".$this->id);

        if ($result = $this->_dbqt->exec()->fetchRow()) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get the 'opposite' Side, the Character is fighting at
     * @param int|Character $char Character ID or Character Object
     * @return string defenders|attackers|neutrals
     */
    public function getOppositeSide($char)
    {
        $result = $this->getMemberEntry($char);

        if ($result['side'] == BATTLE_SIDE_ATTACKERS) {
            return BATTLE_SIDE_DEFENDERS;
        } elseif ($result['side'] == BATTLE_SIDE_DEFENDERS) {
            return BATTLE_SIDE_ATTACKERS;
        } else {
            return BATTLE_SIDE_NEUTRALS;
        }
    }

    /**
     * Add a single Battle Message to the Database
     * @param string $message
     */
    public function addResultMessage($message)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $data = array(
                        "battleid"	=> $this->id,
                        "date" 		=> date("Y-m-d H:i:s"),
                        "message" 	=> $message );

        $result = $this->_dbqt	->insertinto("battlemessages")
                                ->data($data)
                                ->exec();

        return $result;
    }

    /**
     * Write the Battle Messages to Database
     * @param array $messages
     */
    public function addResultMessages(array $messages)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $this->_dbqt->insertinto("battlemessages");

        foreach ($messages as $message) {
            $data = array(
                            "battleid"	=> $this->id,
                            "date" 		=> date("Y-m-d H:i:s"),
                            "message" 	=> $message );

            $this->_dbqt->clear("data")
                        ->data($data)
                        ->exec();
        }
    }

    /**
     * Retrieve the Messages from the Database
     * @return array Array of ResultMessages
     */
    public function getResultMessages($newestfirst=true)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $result = $this->_dbqt	->select("*")
                                ->from("battlemessages")
                                ->where("battleid=".$this->id)
                                ->order("id", $newestfirst)
                                ->exec()
                                ->fetchAll();

        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Truncate Messages
     */
    public function resetMessages()
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $result = $this->_dbqt	->deletefrom("battlemessages")
                                ->where("battleid=".$this->id)
                                ->exec();

        return $result;
    }

    /**
     * Set Member Status
     * @param int|Character $char Character ID or Character Object
     * @param int $status New Status (see defines)
     * @return bool true if successful, else false
     */
    public function setMemberStatus($char, $status)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $this->_dbqt->update("battlemembers");

        if ($char instanceof Character) {
            $this->_dbqt->where("characterid=".$char->id);
        } else {
            $this->_dbqt->where("characterid=".$char);
        }
        $this->_dbqt->where("battleid=".$this->id);

        $this->_dbqt->data(array( "status" => (int)$status ));

        if ($result = $this->_dbqt->exec()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set Member Side
     * @param int|Character $char Character ID or Character Object
     * @param int $status New side (attacker, defender, neutral)
     * @return bool true if successful, else false
     */
    public function setMemberSide($char, $side)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $this->_dbqt->update("battlemembers");

        if ($char instanceof Character) {
            $this->_dbqt->where("characterid=".$char->id);
        } else {
            $this->_dbqt->where("characterid=".$char);
        }
        $this->_dbqt->where("battleid=".$this->id);

        $this->_dbqt->data(array( "side" => $side ));

        if ($result = $this->exec()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the Battle token to the new Character
     * @param int|Character $char Character ID or Character Object
     */
    public function setTokenOwner($char)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        // Erase old Token (if exists)
        $this->_dbqt->clear();
        $this->_dbqt->update("battlemembers")
                    ->where("battleid=".$this->id)
                    ->data(array( "token" => 0 ))
                    ->exec();

        // Set new Token
        $this->_dbqt->clear();

        $this->_dbqt->update("battlemembers");

        if ($char instanceof Character) {
            $this->_dbqt->where("characterid=".$char->id);
        } else {
            $this->_dbqt->where("characterid=".$char);
        }
        $this->_dbqt->where("battleid=".$this->id);

        $this->_dbqt->data(array( "token" => 1 ));

        if ($result = $this->_dbqt->exec()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Token Owner
     * @return int Character ID which owns the token
     */
    public function getTokenOwner()
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $result = $this	->_dbqt->select("characterid")
                        ->from("battlemembers")
                        ->where("battleid=".$this->id)
                        ->where("token=1")
                        ->exec()
                        ->fetchOne();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Pass the Token to a new Battle Member
     */
    public function cycleToken()
    {
        $activemembers = $this->getMemberList();

        if (is_array($activemembers)) {
            $charid	= current($activemembers);

            if ($charid != $this->getTokenOwner()) {
                $this->setTokenOwner($charid);
            } else {
                if ($newowner = next($activemembers)) {
                    $this->setTokenOwner($newowner);
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Get List of all Battle Members (excluding Neutrals if $side isn't set)
     * @param bool|string $side Get List of a specific Side
     * @param bool $names Return names instead of ids
     * @param array $status only get Members with one of the given status
     * @return array Array of all Battlemembers
     */
    public function getMemberList($side=false, $names=false, $status=false)
    {
        return $this->_getBattleMemberList($side, $names, $status);
    }

    /**
     * Get List of Attackers
     * @param bool $names Return names instead of ids
     * @return array Array of Attackers
     */
    public function getAttackerList($names=false)
    {
        return $this->_getBattleMemberList(BATTLE_SIDE_ATTACKERS, $names);
    }

    /**
     * Get List of Neutrals
     * @param bool $names Return names instead of ids
     * @return array Array of Neutrals
     */
    public function getNeutralList($names=false)
    {
        return $this->_getBattleMemberList(BATTLE_SIDE_NEUTRALS, $names);
    }

    /**
     * Get List of Defenders
     * @param bool $names Return names instead of ids
     * @return array Array of Defenders
     */
    public function getDefenderList($names=false)
    {
        return $this->_getBattleMemberList(BATTLE_SIDE_DEFENDERS, $names);
    }

    /**
     * Get List of Battlemembers with 0 or less Healthpoints
     * @param bool $names Return names instead of ids
     * @return array Array of beaten Characters
     */
    public function getBeatenList($names=false)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $result	= array ();

        $this->_dbqt->clear();

        $qResult = $this->_dbqt	->select("healthpoints, characterid")
                                ->from("characters")
                                ->join("battlemembers", "battlemembers.characterid = characters.id")
                                ->where("battlemembers.battleid=".$this->id)
                                ->exec()
                                ->fetchAll();

        foreach ($qResult as $battlemember) {
            if ($battlemember['healthpoints'] <= 0) {
                if ($names) {
                    $result[] = Manager\User::getCharacterName($battlemember['characterid']);
                } else {
                    $result[] = $battlemember['characterid'];
                }
            }
        }

        return $result;
    }

    /**
     * Get List of Characters who made their Action
     * @param bool $names Return names instead of ids
     * @return array Array of Characters who used a Skill this round
     */
    public function getActionDoneList($names=false)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $result	= array ();

        $this->_dbqt->clear();

        $this->_dbqt->select("characterid")
                    ->from("battlemembers")
                    ->join("battletable", "battletable.battleid = battlemembers.battleid")
                    ->where("battlemembers.characterid = battletable.initiatorid")
                    ->where("battlemembers.battleid=".$this->id)
                    ->exec();

        foreach ($this->_dbqt->getAll() as $battlemember) {
            if ($names) {
                $result[] = Manager\User::getCharacterName($battlemember['characterid']);
            } else {
                $result[] = $battlemember['characterid'];
            }
        }

        return $result;
    }

    /**
     * Returns div-box with Information about this Battle
     * @param bool $directoutput Output via OutputObject
     * @return string HTML-Code
     */
    public function showBattleInformation($directoutput=true)
    {
        $outputobject = getOutputObject();

        $output = "<div class='floatleft battleinfo'>";
        $output .= "Angreifer: " . implode(", ", $this->getAttackerList(true)) . "`n";
        $output .= "Verteidiger: " . implode(", ", $this->getDefenderList(true)) . "`n";
        $output .= "Timer: " . ($this->getTimer()?$this->getTimer():"inaktiv") . "`n";

        $battleopstr = $this->_getBattleOpString();

        if (!$this->isActive()) {
            $target = $outputobject->url->base."&{$battleopstr}=join&side=".BATTLE_SIDE_ATTACKERS."&battleid=".$this->id;
            $output .= "<a href='?".$target."'>Angreifen</a>";
            $outputobject->nav->add(new Link("", $target));
            $output .= " || ";
            $target = $outputobject->url->base."&{$battleopstr}=join&side=".BATTLE_SIDE_DEFENDERS."&battleid=".$this->id;
            $output .= "<a href='?".$target."'>Verteidigen</a>";
            $outputobject->nav->add(new Link("", $target));
            $output .= " || ";
        }
        $target = $outputobject->url->base."&{$battleopstr}=join&side=".BATTLE_SIDE_NEUTRALS."&battleid=".$this->id;
        $output .= "<a href='?".$target."'>Zuschauen</a>";
        $outputobject->nav->add(new Link("", $target));
        $output .= "</div>";

        if ($directoutput) {
            $outputobject->output($output, true);
        } else {
            return $output;
        }
    }

    /**
     * Returns skillchooser Form
     * @param bool $directoutput Output via OutputObject
     * @return string HTML-Code
     */
    public function showSkillChooser($directoutput=true)
    {
        global $user;
        $output			= "";
        $outputobject 	= getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();
        $memberentry 	= $this->getMemberEntry($user->char);

        if ($memberentry['side'] == BATTLE_SIDE_NEUTRALS
            || $memberentry['status'] == BATTLE_MEMBERSTATUS_BEATEN
            || $memberentry['status'] == BATTLE_MEMBERSTATUS_EXCLUDED) {
            // Caller is Neutral
            $output .= "Beobachte den Kampf...";
        } elseif ($memberentry['actiondone']) {
            // Caller made his Action
            $output .= "Warte auf andere Kämpfer...";
        } else {
            // Show the Skillchooser
            $skillform = new Form();
            $output .= $skillform->head("skillchooser", $outputobject->url->base."&{$battleopstr}=use_skill");

            // Add Nav
            $outputobject->nav->add(new Link("", $outputobject->url->base."&{$battleopstr}=use_skill"));

            // TODO: Get Available Skills for this Character
            $skills = array ( "punch", "heal", "wait" );

            $skillform->setCSS("input");
            $output .= $skillform->selectStart("skill");
            foreach ($skills as $skill) {
                $skilldata = ModuleSystem::getSkillModule($skill);
                $output .= "<option	value='". $skill."'
                                    title='".$skilldata->description."'
                                    >".$skilldata->name."</option>";
            }
            $output .= $skillform->selectEnd();

            $output .= $skillform->selectStart("target");
            $output .= $skillform->selectEnd();

            $output .= $skillform->submitButton("Ausführen");
            $output .= $skillform->close();
            $output .= "<span id='skilldescription' class='floatclear'></span>";

            // Target-Chooser
            // The third Parameter is the name of the select-Form where we choose the skill
            // The third Parameter is the name of the select-Form where the targets appear
            $outputobject->addJavaScript("$(function(){
                                        getTargetList(".$this->id.", ".$user->char->id.", 'skill', 'target');
            });");
        }

        if ($directoutput) {
            $outputobject->output($output, true);
        } else {
            return $output;
        }
    }

    /**
     * Returns Battle Member List
     * @param bool $directoutput Output via OutputObject
     * @return string HTML-Code
     */
    public function showBattleMemberlist($directoutput=true)
    {
        $outputobject 	= getOutputObject();

        $output = "";

        foreach (array(BATTLE_SIDE_ATTACKERS=>"Angreifer", BATTLE_SIDE_DEFENDERS=>"Verteidiger") as $sysname=>$realname) {
            $output .= "`n$realname: `n";

            $temparray = array();
            foreach ($this->getMemberList($sysname) as $memberid) {
                $chartype = Manager\User::getCharacterType($memberid);
                $member = new $chartype;
                $member->load($memberid);
                if ($member->madeBattleAction($this->id)) {
                    $transparentstyle = "style=\"opacity: 0.5; filter: alpha(opacity=50); filter: 'progid:DXImageTransform.Microsoft.Alpha(Opacity=50, FinishOpacity=50, Style=2)'\"";
                } else {
                    $transparentstyle = "";
                }
                $temparray[] = "<span id='action_".$member->id."' $transparentstyle>".btCode::decode($member->displayname)." HP: ".$member->healthpoints."/".$member->lifepoints."</span>";
            }

            $output .= implode(", ", $temparray);
        }

        $output .= "`nZuschauer: `n";
        $output .= implode(", ", $this->getNeutralList(true));

        if ($directoutput) {
            $outputobject->output($output, true);
        } else {
            return $output;
        }
    }

    public function showResultStats($directoutput=true)
    {
        $outputobject 	= getOutputObject();

        $output 	= "";

        $beforeSS	= $this->battlemembersnapshot;
        $afterSS	= $this->_getBattleMemberSnapshot();

        if (!is_array($beforeSS) || !is_array($afterSS)) {
//			return $output;
        }

        foreach ($beforeSS['data'] as $memberid => $memberdata) {
            $output .= Manager\User::getCharacterName($memberid, true) . ": `n";

            $bminfo	= $this->getMemberEntry($memberid);
            $status = "";

            switch ($bminfo) {
                case BATTLE_MEMBERSTATUS_ACTIVE: $status = "Aktiv"; break;
                case BATTLE_MEMBERSTATUS_INACTIVE: $status = "Inaktiv"; break;
                case BATTLE_MEMBERSTATUS_EXCLUDED: $status = "Ausgeschlossen"; break;
                case BATTLE_MEMBERSTATUS_BEATEN: $status = "Tot"; break;
            }

            foreach ($beforeSS['description'] as $property) {
                $output .= 	$property . ": " .
                            $beforeSS['data'][$memberid][$property] .
                            " -> " .
                            $afterSS['data'][$memberid][$property] .
                            " ({$status}) " .
                            "`n";
            }

        }

        if ($directoutput) {
            $outputobject->output($output, true);
        } else {
            return $output;
        }

    }

    /**
     * Add Battle Navigation (in Battle)
     */
    public function addBattleNav()
    {
        $outputobject 	= getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->add(new Link("Kampf"));
        $outputobject->nav->add(new Link("Fliehen", $outputobject->url->base."&{$battleopstr}=flee"));
    }

    /**
     * Add Battle Navigation (before Battle)
     */
    public function addCreateBattleNav()
    {
        $outputobject 	= getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->add(new Link("Kampf"));
        $outputobject->nav->add(new Link("Anfangen", $outputobject->url->base."&{$battleopstr}=create"));
    }

    /**
     * Add Battle Navigation (in Battle)
     */
    public function addAdminBattleNav()
    {
        $outputobject 	= getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->add(new Link("Admin"));
        $outputobject->nav->add(new Link("Kampf Beenden", $outputobject->url->base."&{$battleopstr}=admin_remove"));
        $outputobject->nav->add(new Link("Nachrichten Löschen", $outputobject->url->base."&{$battleopstr}=admin_removemessages"));
    }

    /**
     * Get List of a given Side (attackers, neutrals oder defenders)
     * @param $side attackers, neutrals or defenders
     * @param bool $names Return names instead of ids
     * @param array $status Only get Members with the given status
     * @return array of characterids
     */
    private function _getBattleMemberList($side=false, $names=false, $status=false)
    {
        if (!$result = SessionStore::readCache("battlememberlist_".$this->id."_".$side."_".$names."_".$status)) {

            $this->_dbqt->clear();

            $this->_dbqt->select("characterid")
                        ->from("battlemembers")
                        ->where("battleid=".$this->id);

            if ($side) {
                $this->_dbqt->where("side=".$this->_dbqt->quote(strtolower($side)));
            } else {
                $this->_dbqt->where("side!=".$this->_dbqt->quote(strtolower(BATTLE_SIDE_NEUTRALS)));
            }

            if ($status) {
                if (is_array($status)) {
                    foreach($status as &$value) {
                        $value = "status=".$this->_dbqt->quote($value);
                    }
                    $sqlstring = "(";
                    $sqlstring .= implode(" OR ", $status);
                    $sqlstring .= ")";
                } else {
                    $sqlstring = "status=" . $this->_dbqt->quote($status);
                }
                $this->_dbqt->where($sqlstring);
            }

            if ($qResult = $this->_dbqt->exec()->fetchCol("characterid")) {
                $result = $qResult;
            } else {
                $result = array();
            }

            if ($names) {
                $tempres = array();
                foreach ($result as $id) {
                    $tempres[] = Manager\User::getCharacterName($id);
                }
                $result = $tempres;
            }

            SessionStore::writeCache("battlememberlist_".$this->id."_".$side."_".$names."_".$status, $result, "page");
        }

        return $result;
    }

    /**
     * Choose Skill to execute
     * @param Character $char
     * @param mixed $target
     * @param Skill $action The Skill to use
     * @return bool true if successful, else false
     */
    public function chooseSkill(Character &$char, $target, Skill $skill)
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        // Check if the Char is Part of this Battle
        if (!$this->isMember($char) || $char->madeBattleaction($this->id)) {
            return false;
        }

        // Add the Action to the battletable
        $this->_dbqt->clear();
        $data = array(
                        "battleid" => $this->id,
                        "initiatorid" => $char->id,
                        "target" => $target,
                        "skill" => get_class($skill) );

        $this->_dbqt->insertinto("battletable")
                    ->data($data)
                    ->exec();


        // Set actiondone-flag
        $this->_dbqt->clear();
        $data = array( 	"actiondone" => true,
                        "status" => BATTLE_MEMBERSTATUS_ACTIVE );

        $this->_dbqt->update("battlemembers")
                    ->where("battleid=".$this->id)
                    ->where("characterid=".$char->id)
                    ->data($data)
                    ->exec();

        return true;
    }

    /**
     * Check for beaten Members and move them to Neutrals with the beaten-flag given
     */
    public function checkBeatenMembers()
    {
        $beatenlist = $this->getBeatenList();

        foreach ($beatenlist as $member) {
            $displayname = Manager\User::getCharacterName($member['characterid']);

            // Member is beaten
            $this->addResultMessage($displayname . " wurde besiegt!");

            // Set status to beaten
            $this->setMemberStatus($member['characterid'], BATTLE_MEMBERSTATUS_BEATEN);

            // Remove Token from this Member
            if ($this->getTokenOwner() == $member['characterid']) {
                if(!$this->cycleToken()) {
                    // Can't find a new, valid Tokenowner
                    $this->finish();
                }
            }
        }
    }

    /**
     * Check if all premises are set and we are able to start the action
     * @return bool true if everything is in order and the round can begin, else false
     */
    public function checkPremise()
    {
        // First Check if one Side has less than 1 member
        if (count($this->getMemberList(BATTLE_SIDE_ATTACKERS)) < 1
            || count($this->getMemberList(BATTLE_SIDE_DEFENDERS)) < 1) {
            return false;
        }

        // Check if the Timer ran out
        if ( !($this->getTimer()) ) {
            return true;
        }

        // Check if all Members made an action
        $this->_dbqt->clear();

        $result = $this->_dbqt	->select("*")
                                ->from("battlemembers")
                                ->where("battleid=".$this->id)
                                ->where("actiondone=0")
                                ->where("( status=".$this->_dbqt->quote(BATTLE_MEMBERSTATUS_ACTIVE))
                                ->where("status=".$this->_dbqt->quote(BATTLE_MEMBERSTATUS_INACTIVE). " )", "OR")
                                ->exec()
                                ->numRows();

        if ($result == 0) {
            return true;
        }

        return false;
    }

    /**
     * Return the Battle Table, sorted by the Characterspeed (fastest first)
     * @return array Battle Table
     */
    private function _getSortedBattleTableByCharacterSpeed()
    {
        $this->_dbqt->clear();

        $result = $this->_dbqt	->select("*")
                                ->from("battletable")
                                ->join("battlemembers", "battlemembers.characterid = battletable.initiatorid")
                                ->where("battletable.battleid=".$this->id)
                                ->where("battlemembers.battleid = battletable.battleid")
                                ->where("( status=".$this->_dbqt->quote(BATTLE_MEMBERSTATUS_ACTIVE))
                                ->where("status=".$this->_dbqt->quote(BATTLE_MEMBERSTATUS_INACTIVE). " )", "OR")
                                ->order("battlemembers.speed", true)
                                ->order("battlemembers.side") // attackers before defenders if speed is equal
                                ->exec()
                                ->fetchAll();

        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Generate battle op-string
     * @return string
     */
    private function _getBattleOpString()
    {
        return "battle_op";
    }

    /**
     * Add Battle Helper-Functions and Parameter-Handler
     */
    private function _addHelper()
    {
        global $user;

        $battleop 		= $this->_getBattleOpString();
        $outputobject 	= getOutputObject();

        if ($battleid = $user->char->isInABattle()) {
            // Load the Battle
            $this->load($battleid);

            // Battle JavaScript
            $outputobject->addJavaScriptFile("jquery.plugin.timers.js");
            $outputobject->addJavaScriptFile("battle.func.js");

            // Add Autorefresher + Statuschecker
            if ($this->getTokenOwner() == $user->char->id) {
                // We own the token
                // Refresh the Page if every Battlemember has made his move
                $outputobject->addJavaScript("$(function(){
                                checkBattleActionDone(".$this->id.", true);
                });");
            } else {
                // We're not the token owner
                // Refresh the Page if a new Round is created by the Token Owner
                $outputobject->addJavaScript("$(function(){
                                checkBattleActionDone(".$this->id.");
                                refreshOnNewRound(".$user->char->id.");
                });");
            }


            // Check if every Battlemember made his move or the time ran out
            // Calculate the Result of this Round
            if ($this->checkPremise()) {
                if ($this->getTokenOwner() == $user->char->id) {
                    $this->calculate();
                }
            }
        }



        if (!isset($_GET[$battleop])) {
            $_GET[$battleop] = "";
        }

        switch ($_GET[$battleop]) {

            case "join":
                // Join a Battle
                if (isset($_GET['battleid']) && isset($_GET['side'])) {
                    $this->load($_GET['battleid']);
                    $this->addMember($user->char, $_GET['side']);
                }

                $outputobject->refresh(true);
                break;

            case "part":
                // Part a Battle
                $this->removeMember($user->char);

                $outputobject->refresh(true);
                break;

            case "flee":
                // Part a Battle
                $this->removeMember($user->char);

                $outputobject->refresh(true);
                break;

            case "create":
                // Create a new Battle
                $this->initialize();

                // Automatically add the Creator to the attackers and give him the token
                $this->addMember($user->char, BATTLE_SIDE_ATTACKERS);
                $this->setTokenOwner($user->char);

                $outputobject->refresh(true);
                break;

            case "use_skill":
                if (isset($_POST['skill']) && isset($_POST['target'])) {
                    $this->chooseSkill($user->char, $_POST['target'], ModuleSystem::getSkillModule($_POST['skill']));
                }

                $outputobject->refresh(true);
                break;

            case "admin_remove":
                // Delete the Battle
                $this->finish();

                $outputobject->refresh(true);
                break;

            case "admin_removemessages":
                // Clean the Battlemessages
                $this->resetMessages();

                $outputobject->refresh(true);
                break;
        }
    }

    /**
     * Set the default action if none is set in time
     */
    private function _setDefaultAction()
    {
        global $dbconnect;

        // First get the Users which didn't make an action
        $this->_dbqt->clear();

        $subsel = new QueryTool;

        $characterdata = $this->_dbqt	->select("characterid, status")
                                        ->from("battlemembers")
                                        ->where("battleid=".$this->id)
                                        ->where("characterid NOT IN (" .
                                            $subsel	->select("initiatorid")
                                                    ->from("battletable")
                                                    ->where("battleid=".$this->id)
                                        . ")")
                                        ->exec()
                                        ->fetchAll();

        if ($characterdata) {
            // Set the default action for the rest of the users
            foreach ($characterdata as $entry) {
                $chartype = Manager\User::getCharacterType($entry['characterid']);

                $tempchar = new $chartype;
                $tempchar->load($entry['characterid']);

                if ($entry['status'] == BATTLE_MEMBERSTATUS_ACTIVE) {
                    // First inactive Round
                    $this->chooseSkill($tempchar, "none", ModuleSystem::getSkillModule("wait"));
                    $this->setMemberStatus($tempchar, BATTLE_MEMBERSTATUS_INACTIVE);
                } elseif ($entry['status'] == BATTLE_MEMBERSTATUS_INACTIVE) {
                    // Second inactive Round => exclude Battlemember
                    $this->setMemberStatus($tempchar, BATTLE_MEMBERSTATUS_EXCLUDED);
                    $this->addResultMessage($tempchar->displayname . " wurde wegen Inaktivität vom Kampf ausgeschlossen.");
                }
            }
        } else {
            // all Users made an action
            return true;
        }
    }


    /**
     * Create a BattleMember Snapshot
     * A Snapshot array looks like this:
     * array (
     *     description => array ( "healthpoints" ),
     *     data	=> array (
     *         3 => array (
     *             "healthpoints" => 10
     *         )
     *         4 => array (
     *             "healthpoints" => 8
     *         }
     *     )
     * )
     */
    private function _getBattleMemberSnapshot()
    {
        $ssdata					= array( "description"=>array(), "data"=>array());
        $ssfields 				= array ( "healthpoints" );

        // copy data-description
        $ssdata['description']	= $ssfields;

        foreach ($this->getMemberList() as $memberid) {
            $chartype = Manager\User::getCharacterType($memberid);

            $tempchar = new $chartype;
            $tempchar->load($memberid);

            $ssdata['data'][$memberid] = array();

            foreach ($ssfields as $property) {
                $ssdata['data'][$memberid][$property] = $tempchar->{$property};
            }
        }

        return $ssdata;
    }

    /**
     * Create and Write a Battle Member Snapshot
     */
    private function _writeBattleMemberSnapshot()
    {
        $this->battlemembersnapshot = $this->_getBattleMemberSnapshot();
    }

    /**
     * Check if this Battle is active
     * @return bool true if the battle is active, else false
     */
    public function isActive()
    {
        if ($this->isloaded && $this->active) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Start the Battle
     */
    public function startBattle()
    {
        if (!$this->isActive()) {
            // Create Battle Member Snapshot
            $this->_writeBattleMemberSnapshot();

            // Start Timer
            $this->startTimer();

            // Set Active-Flag
            $this->active = true;
        }
    }

    /**
     * Calculate the Battle, execute every Skill
     */
    public function calculate()
    {
        // Start the Battle if it isn't already running
        $this->startBattle();

        // Finish the Battle if there aren't enough BattleMembers to fight
        $fightmembers = $this->getMemberList(false, false, array(
                                                                    BATTLE_MEMBERSTATUS_ACTIVE,
                                                                    BATTLE_MEMBERSTATUS_INACTIVE) );

        if (count($fightmembers) <= 1) {
            $this->finish();
            return;
        }

        // Set default Action if no action is set in time
        $this->_setDefaultAction();

        // Get Actiontable for this Battle, sorted by CharacterSpeed
        $battletable = $this->_getSortedBattleTableByCharacterSpeed();

        if (!$battletable) {
            $this->finish();
            return;
        }

        foreach ($battletable as $action) {
            // Load the Skill
            $skill = ModuleSystem::getSkillModule($action['skill']);

            // Give the Skill the current Battle environment
            $skill->setBattleEnvironment($this);

            // Prepare the Skill
            $skill->prepare($action);

            // Activate the Skill
            $skill->activate();

            // Cleanup Skillaction (saves changed Characters, etc)
            $skill->finish();

            // Save Result Message
            $this->addResultMessages($skill->resultmessages);
        }

        // Check for beaten Characters
        $this->checkBeatenMembers();

        // Reset Battletimer
        $this->resetTimer();

        // Reset ActionDone-Flag
        $this->resetActionFlag();

        // Reset Battle Table
        $this->resetBattleTable();

        // Increase Round Number
        $this->round = $this->round+1;
    }

    /**
     * Finish the Battle
     */
    public function finish()
    {
        // Stop the Timer
        $this->stopTimer();

        // Show Result Stats if the battle started
        if ($this->active) {
            $this->showResultStats();
        }

        // Remove Battle
        $this->removeBattle();
    }

    /**
     * Return the Battletimer
     * @return string Current Battletimer wrapped in divs (see Timer::get())
     */
    public function getTimer()
    {
        if (!($this->_timer instanceof Timer)) {
            $this->_timer = new Timer($this->battletimer);
        }

        return $this->_timer->get();
    }

    /**
     * Initialize/Set Battletimer
     */
    public function initTimer()
    {
        global $config;

        $timername = uniqid("battle_");
        $this->_timer = new Timer($timername);
        $this->battletimer = $timername;

        // Set Roundtime
        $this->_timer->set($config->get("battle_roundtime", 120));

        // Stop timer for now
        $this->_timer->stop();
    }

    /**
     * Reset Battletimer
     */
    public function resetTimer()
    {
        global $config;

        if (!($this->_timer instanceof Timer)) {
            $this->_timer = new Timer($this->battletimer);
        }

        $this->_timer->set($config->get("battle_roundtime", 120), 0, 0, true);
    }

    /**
     * Start Battletimer
     */
    public function startTimer()
    {
        if (!($this->_timer instanceof Timer)) {
            $this->_timer = new Timer($this->battletimer);
        }

        $this->_timer->start();
    }

    /**
     * Stop Battletimer
     */
    public function stopTimer()
    {
        if (!($this->_timer instanceof Timer)) {
            $this->_timer = new Timer($this->battletimer);
        }

        $this->_timer->stop();
    }

    /**
     * Reset ActionFlag
     */
    public function resetActionFlag()
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $data = array( "actiondone" => false );

        $this->_dbqt->update("battlemembers")
                    ->where("battleid=".$this->id)
                    ->data($data)
                    ->exec();
    }

    /**
     * Reset Battle Table
     */
    public function resetBattleTable()
    {
        // Check if this Battle is initialized
        if (!$this->initialized) {
            return false;
        }

        $this->_dbqt->clear();

        $this->_dbqt->deletefrom("battletable")
                    ->where("battleid=".$this->id)
                    ->exec();
    }

    /**
     * @see includes/classes/BaseObject#mod_postload()
     */
    public function mod_postload()
    {
        // an existing, loaded Battle is alway initialized
        $this->initialized = true;
    }
}
?>
