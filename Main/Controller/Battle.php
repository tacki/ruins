<?php
/**
 * Battle Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Controller;
use Common\Controller\SessionStore,
    Common\Controller\Error,
    Main\Entities\Character,
    Main\Entities,
    Main\Manager;


/**
 * Battle Class
 *
 * @package Ruins
 */
class Battle
{
    /**
     * Class constants
     */
    const SIDE_ATTACKERS        = "attackers";
    const SIDE_DEFENDERS        = "defenders";
    const SIDE_NEUTRALS         = "neutrals";
    const MEMBERSTATUS_ACTIVE   = 0;
    const MEMBERSTATUS_INACTIVE = 1;
    const MEMBERSTATUS_EXCLUDED = 2;
    const MEMBERSTATUS_BEATEN   = 4;

    /**
     * Battle Object
     * @var Main\Entities\Battle
     */
    private $_battle;

    /**
     * Battle Timer Controlling Object
     * @var Main\Controller\Timer
     */
    private $_battleTimerControl;

    /**
     * constructor - load the default values and initialize the attributes
     */
    public function __construct()
    {
        // Add the Helper Functions
        // Only if we have an OutputObject
        if (getOutputObject()) {
            $this->_addHelper();
        }
    }

    /**
     * Initialize for a new Battle
     */
    public function initialize()
    {
        global $em, $user;

        // Create Timer Entity
        $battletimer               = new Entities\Timer;
        $battletimer->name         = uniqid("battle_");
        $em->persist($battletimer);

        // Create Battle Entity
        $this->_battle             = new Entities\Battle;
        $this->_battle->initiator  = $user->character;
        $this->_battle->timer      = $battletimer;
        $this->round               = 1;
        $em->persist($this->_battle);

        $em->flush();

        // Initialize Battletick-Timer
        $this->initTimer();
    }

    public function load($battleid)
    {
        global $em;

        // Check if this Battle is already initialized
        if ($this->_battle) {
            return false;
        }

        $this->_battle = $em->find("Main:Battle", $battleid);
    }

    /**
     * Check if a Character is a Member of this Battle
     * @param Character $char Character to check
     * @return bool true if the char is a Member, else false
     */
    public function isMember(Character $character)
    {
        return $this->_battle->isMember($character);
    }

    public function getMember(Character $character)
    {
        return $this->_battle->getMember($character);
    }

    /**
     * Add a new Member to the Battle
     * @param Character $character
     * @param string $side The side the Character joins (attacker, defender, ...)
     * @return bool true if successful, else false
     */
    public function addMember(Character $character, $side)
    {
       // Check if this Battle is initialized
        if (!$this->_battle) {
            throw new Error("Cannot add a Member to a Battle if it's not initialized");
        }

        $this->_battle->addMember($character, $side);
    }

    /**
     * Remove a Member from the Battle
     * @param Character $char The Character to remove
     * @return bool true if successful, else false
     */
    public function removeMember(Character $character)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        // Cycle Token if this is the token-owner
        if ($this->getTokenOwner() === $character) {
            // Give the Token to another Battle Member
            $this->cycleToken();
        }

        $this->_battle->removeMember($character);

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
        global $em;

        if ($this->_battle) {
            $em->remove($this->_battle);
        }

        $this->_battle = NULL;
    }

    /**
     * Get the 'opposite' Side, the Character is fighting at
     * @param Character $character Character ID or Character Object
     * @return string defenders|attackers|neutrals
     */
    public function getOppositeSide(Character $character)
    {
        $result = $this->getMember($character);

        if ($result->side == self::SIDE_ATTACKERS) {
            return self::SIDE_DEFENDERS;
        } elseif ($result->side == self::SIDE_DEFENDERS) {
            return self::SIDE_ATTACKERS;
        } else {
            return self::SIDE_NEUTRALS;
        }
    }

    /**
     * Add a single Battle Message to the Database
     * @param string $message
     */
    public function addResultMessage($message)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        $this->_battle->addMessage($message);
    }

    /**
     * Write the Battle Messages to Database
     * @param array $messages
     */
    public function addResultMessages(array $messages)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        foreach ($messages as $message) {
            $this->addResultMessage($message);
        }
    }

    /**
     * Retrieve the Messages from the Database
     * @return \Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMessage
     */
    public function getResultMessages()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        return $this->_battle->messages;
    }

    /**
     * Truncate Messages
     */
    public function resetMessages()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }


        $this->_battle->messages->clear();

        return $result;
    }

    /**
     * Set Member Status
     * @param Character $char Character ID or Character Object
     * @param int $status New Status (see defines)
     */
    public function setMemberStatus(Character $character, $status)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        $memberEntry = $this->getMember($character);
        $memberEntry->status = $status;
    }

    /**
     * Set Member Side
     * @param Character $char Character ID or Character Object
     * @param int $status New side (attacker, defender, neutral)
     * @return bool true if successful, else false
     */
    public function setMemberSide(Character $character, $side)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        $memberEntry = $this->getMember($character);
        $memberEntry->side = $side;
    }

    /**
     * Set the Battle token to the new Character
     * @param Character $char Character Object
     */
    public function setTokenOwner(Character $character)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        // Erase Token from all Members
        foreach ($this->_battle->members as $member) {
            $member->token = false;
        }

        // Set new Token
        $tokenOwner = $this->getMember($character);
        $tokenOwner->token = true;

        // Erase old Token (if exists)
        $qb = getQueryBuilder();

        return true;
    }

    /**
     * Get Token Owner
     * @return Character Character which owns the token
     */
    public function getTokenOwner()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        foreach ($this->_battle->members as $member) {
            if ($member->token) {
                return $member->character;
            }
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
    public function getMemberList($side=false, array $status=array())
    {
        return $this->_getBattleMemberList($side, $status);
    }

    /**
     * Get List of Attackers
     * @return array Array of Attackers
     */
    public function getAttackerList()
    {
        return $this->_getBattleMemberList(self::SIDE_ATTACKERS);
    }

    /**
     * Get List of Neutrals
     * @return array Array of Neutrals
     */
    public function getNeutralList()
    {
        return $this->_getBattleMemberList(self::SIDE_NEUTRALS);
    }

    /**
     * Get List of Defenders
     * @return array Array of Defenders
     */
    public function getDefenderList()
    {
        return $this->_getBattleMemberList(self::SIDE_DEFENDERS);
    }

    /**
     * Get List of Battlemembers with 0 or less Healthpoints
     * @return array Array of beaten Characters
     */
    public function getBeatenList()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        $result = array();

        foreach ($this->_battle->members as $member) {
            if ($member->character->healthpoints <= 0) {
                $result[] = $member;
            }
        }

        return $result;
    }

    /**
     * Get List of Characters who made their Action
     * @return array Array of Characters who used a Skill this round
     */
    public function getActionDoneList()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        if (is_array($this->_battle->members) && is_array($this->_battle->actions)) {
            $result = array_intersect($this->_battle->members, $this->_battle->actions);
        } else {
            $result = array();
        }
/*
        $result	= array ();

        foreach ($this->_battle->members as $member) {
            foreach ($this->_battle->actions as $action) {
                if ($member->character ==) $action->character) $result[] = $member;
            }
        }
*/

        return $result;
/*
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
*/
    }

    /**
     * Returns div-box with Information about this Battle
     * @param bool $directoutput Output via OutputObject
     * @return string HTML-Code
     */
    public function showBattleInformation($directoutput=true)
    {
        $outputobject = getOutputObject();

        $attackerlist = $this->getAttackerList();
        $defenderlist = $this->getDefenderList();

        $output = "<div class='floatleft battleinfo'>";
        if (count($attackerlist)) {
            $output .= "Angreifer: ";
            foreach ($attackerlist as $entry) {
                $output .= $entry->character->displayname . " ";
            }
            $output .= "`n";
        }
        if (count($defenderlist)) {
            $output .= "Verteidiger: ";
            foreach ($defenderlist as $entry) {
                $output .= $entry->character->displayname . " ";
            }
            $output .= "`n";
        }
        $output .= "Timer: " . ($this->getTimer()?$this->getTimer():"inaktiv") . "`n";

        $battleopstr = $this->_getBattleOpString();

        if (!$this->isActive()) {
            $target = $outputobject->url->base."&{$battleopstr}=join&side=".self::SIDE_ATTACKERS."&battleid=".$this->id;
            $output .= "<a href='?".$target."'>Angreifen</a>";
            $outputobject->nav->addHiddenLink($target);
            $output .= " || ";
            $target = $outputobject->url->base."&{$battleopstr}=join&side=".self::SIDE_DEFENDERS."&battleid=".$this->id;
            $output .= "<a href='?".$target."'>Verteidigen</a>";
            $outputobject->nav->addHiddenLink($target);
            $output .= " || ";
        }
        $target = $outputobject->url->base."&{$battleopstr}=join&side=".self::SIDE_NEUTRALS."&battleid=".$this->id;
        $output .= "<a href='?".$target."'>Zuschauen</a>";
        $outputobject->nav->addHiddenLink($target);
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
        $memberentry 	= $this->getMember($user->character);

        if ($memberentry['side'] == self::SIDE_NEUTRALS
            || $memberentry['status'] == self::MEMBERSTATUS_BEATEN
            || $memberentry['status'] == self::MEMBERSTATUS_EXCLUDED) {
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
            $outputobject->nav->addHiddenLink($outputobject->url->base."&{$battleopstr}=use_skill");

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
                                        getTargetList(".$this->id.", ".$user->character->id.", 'skill', 'target');
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

        foreach (array(self::SIDE_ATTACKERS=>"Angreifer", self::SIDE_DEFENDERS=>"Verteidiger") as $sysname=>$realname) {
            $output .= "`n$realname: `n";

            $temparray = array();
            foreach ($this->getMemberList($sysname) as $member) {

                if ($member->actiondone) {
                    $transparentstyle = "style=\"opacity: 0.5; filter: alpha(opacity=50); filter: 'progid:DXImageTransform.Microsoft.Alpha(Opacity=50, FinishOpacity=50, Style=2)'\"";
                } else {
                    $transparentstyle = "";
                }
                $temparray[] = "<span id='action_".$member->character->id."' $transparentstyle>".$member->character->displayname." HP: ".$member->character->healthpoints."/".$member->character->lifepoints."</span>";
            }

            $output .= implode(", ", $temparray);
        }

        $neutrallist = $this->getNeutralList(true);

        if (count($neutrallist)) {
            $output .= "`nZuschauer: `n";
            foreach ($neutrallist as $entry) {
                $output .= $entry->character->displayname . " ";
            }
            $output .= "`n";
        }

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

            $bminfo	= $this->getMember($memberid);
            $status = "";

            switch ($bminfo) {
                case self::MEMBERSTATUS_ACTIVE: $status = "Aktiv"; break;
                case self::MEMBERSTATUS_INACTIVE: $status = "Inaktiv"; break;
                case self::MEMBERSTATUS_EXCLUDED: $status = "Ausgeschlossen"; break;
                case self::MEMBERSTATUS_BEATEN: $status = "Tot"; break;
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

        $outputobject->nav->addHead("Kampf")
                          ->addLink("Fliehen", $outputobject->url->base."&{$battleopstr}=flee");
    }

    /**
     * Add Battle Navigation (before Battle)
     */
    public function addCreateBattleNav()
    {
        $outputobject 	= getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->addHead("Kampf")
                          ->addLink("Anfangen", $outputobject->url->base."&{$battleopstr}=create");
    }

    /**
     * Add Battle Navigation (in Battle)
     */
    public function addAdminBattleNav()
    {
        $outputobject 	= getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->addHead("Admin")
                          ->addLink("Kampf Beenden", $outputobject->url->base."&{$battleopstr}=admin_remove")
                          ->addLink("Nachrichten Löschen", $outputobject->url->base."&{$battleopstr}=admin_removemessages");
    }

    /**
     * Get List of a given Side (attackers, neutrals oder defenders)
     * @param $side attackers, neutrals or defenders
     * @param bool $names Return names instead of ids
     * @param array $status Only get Members with the given status
     * @return array of BattleMembers
     */
    private function _getBattleMemberList($side=false, array $status=array())
    {
        $result = array();

        foreach ($this->_battle->members as $member) {
            if ($side && $member->side == $side) {
                if (in_array($member->status, $status)) {
                    // Both are set and correct
                    $result[] = $member;
                } elseif (empty($status)) {
                    // Only Side is set and correct
                    $result[] = $member;
                }
            } elseif (in_array($member->status, $status)) {
                // Only Status is set and correct
                $result[] = $member;
            } elseif (empty($status) && $side === false) {
                // All Battlemembers
                $result = $this->_battle->members;
            }
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
    public function chooseSkill(Character $character, $target, Skill $skill)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        // Check if the Char is Part of this Battle
        if (!$this->isMember($character) || $this->_battle->hasActionDone($character)) {
            return false;
        }

        // Add the Action to the battletable
        $this->_battle->addAction($character, $target, $skill);

        // Mark Member as Active
        $this->setMemberStatus($character, self::MEMBERSTATUS_ACTIVE);

/*
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
                        "status" => self::MEMBERSTATUS_ACTIVE );

        $this->_dbqt->update("battlemembers")
                    ->where("battleid=".$this->id)
                    ->where("characterid=".$char->id)
                    ->data($data)
                    ->exec();

        return true;
*/
    }

    /**
     * Check for beaten Members and move them to Neutrals with the beaten-flag given
     */
    public function checkBeatenMembers()
    {
        $beatenlist = $this->getBeatenList();

        foreach ($beatenlist as $member) {
            // Member is beaten
            $this->addResultMessage($member->displayname . " wurde besiegt!");

            // Set status to beaten
            $this->setMemberStatus($member, self::MEMBERSTATUS_BEATEN);

            // Remove Token from this Member
            if ($member->token) {
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
        if (count($this->getMemberList(self::SIDE_ATTACKERS)) < 1
            || count($this->getMemberList(self::SIDE_DEFENDERS)) < 1) {
            return false;
        }

        // Check if the Timer ran out
        if ( !($this->getTimer()) ) {
            return true;
        }

        foreach($this->getMemberList() as $member) {
            if (!$member->actiondone) {
                return false;
            }
        }

        return true;
 /*
        // Check if all Members made an action
        $this->_dbqt->clear();

        $result = $this->_dbqt	->select("*")
                                ->from("battlemembers")
                                ->where("battleid=".$this->id)
                                ->where("actiondone=0")
                                ->where("( status=".$this->_dbqt->quote(self::MEMBERSTATUS_ACTIVE))
                                ->where("status=".$this->_dbqt->quote(self::MEMBERSTATUS_INACTIVE). " )", "OR")
                                ->exec()
                                ->numRows();

        if ($result == 0) {
            return true;
        }

        return false;
*/
    }

    /**
     * Return the Battle Table, sorted by the Characterspeed (fastest first)
     * @return array Battle Table
     */
    private function _getSortedBattleTableByCharacterSpeed()
    {
        // TODO
        $this->_dbqt->clear();

        $result = $this->_dbqt	->select("*")
                                ->from("battletable")
                                ->join("battlemembers", "battlemembers.characterid = battletable.initiatorid")
                                ->where("battletable.battleid=".$this->id)
                                ->where("battlemembers.battleid = battletable.battleid")
                                ->where("( status=".$this->_dbqt->quote(self::MEMBERSTATUS_ACTIVE))
                                ->where("status=".$this->_dbqt->quote(self::MEMBERSTATUS_INACTIVE). " )", "OR")
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

        if ($battleid = Manager\Battle::getBattleID($user->character)) {
            // Load the Battle
            $this->load($battleid);

            // Battle JavaScript
            $outputobject->addJavaScriptFile("jquery.plugin.timers.js");
            $outputobject->addJavaScriptFile("battle.func.js");

            // Add Autorefresher + Statuschecker
            if ($this->getTokenOwner() == $user->character->id) {
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
                                refreshOnNewRound(".$user->character->id.");
                });");
            }


            // Check if every Battlemember made his move or the time ran out
            // Calculate the Result of this Round
            if ($this->checkPremise()) {
                if ($this->getTokenOwner() == $user->character->id) {
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
                    $this->addMember($user->character, $_GET['side']);
                }

                $outputobject->refresh(true);
                break;

            case "part":
                // Part a Battle
                $this->removeMember($user->character);

                $outputobject->refresh(true);
                break;

            case "flee":
                // Part a Battle
                $this->removeMember($user->character);

                $outputobject->refresh(true);
                break;

            case "create":
                // Create a new Battle
                $this->initialize();

                // Automatically add the Creator to the attackers and give him the token
                $this->addMember($user->character, self::SIDE_ATTACKERS);
                $this->setTokenOwner($user->character);

                $outputobject->refresh(true);
                break;

            case "use_skill":
                if (isset($_POST['skill']) && isset($_POST['target'])) {
                    $this->chooseSkill($user->character, $_POST['target'], ModuleSystem::getSkillModule($_POST['skill']));
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
        $battleMembers = $this->_battle->getActionNeededList();

/*
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
*/
        if ($battleMembers) {
            // Set the default action for the rest of the users
            foreach ($battleMembers as $member) {

                if ($entry->status == self::MEMBERSTATUS_ACTIVE) {
                    // First inactive Round
                    $this->chooseSkill($member, "none", ModuleSystem::getSkillModule("wait"));
                    $this->setMemberStatus($member, self::MEMBERSTATUS_INACTIVE);
                } elseif ($entry['status'] == self::MEMBERSTATUS_INACTIVE) {
                    // Second inactive Round => exclude Battlemember
                    $this->setMemberStatus($tempchar, self::MEMBERSTATUS_EXCLUDED);
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
                                                                    self::MEMBERSTATUS_ACTIVE,
                                                                    self::MEMBERSTATUS_INACTIVE) );

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
        if ($this->_battle->active) {
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
        if (!($this->_battleTimerControl instanceof Timer)) {
            $this->_battleTimerControl = new Timer($this->_battle->timer->name);
        }

        return $this->_battleTimerControl->get();
    }

    /**
     * Initialize/Set Battletimer
     */
    public function initTimer()
    {
        global $systemConfig;

        $this->_battleTimerControl = new Timer($this->_battle->timer->name);

        // Set Roundtime
        $this->_battleTimerControl->set($systemConfig->get("battle_roundtime", 120));

        // Stop timer for now
        $this->_battleTimerControl->stop();
    }

    /**
     * Reset Battletimer
     */
    public function resetTimer()
    {
        global $systemConfig;

        if (!($this->_battleTimerControl instanceof Timer)) {
            $this->_battleTimerControl = new Timer($this->_battle->timer->name);
        }

        $this->_battleTimerControl->set($systemConfig->get("battle_roundtime", 120), 0, 0, true);
    }

    /**
     * Start Battletimer
     */
    public function startTimer()
    {
        if (!($this->_battleTimerControl instanceof Timer)) {
            $this->_battleTimerControl = new Timer($this->_battle->timer->name);
        }

        $this->_battleTimerControl->start();
    }

    /**
     * Stop Battletimer
     */
    public function stopTimer()
    {
        if (!($this->_battleTimerControl instanceof Timer)) {
            $this->_battleTimerControl = new Timer($this->_battle->timer->name);
        }

        $this->_battleTimerControl->stop();
    }

    /**
     * Reset ActionFlag
     */
    public function resetActionFlag()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        foreach ($this->_battle->members as $member) {
            $member->actiondone = false;
        }
    }

    /**
     * Reset Battle Table
     */
    public function resetBattleTable()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        $this->_battle->actions->clear();
    }
}
?>
