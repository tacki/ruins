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
        if (Manager\System::getOutputObject()) {
            $this->_addHelper();
        }
    }

    /**
     * Initialize for a new Battle
     */
    public function initialize()
    {
        global $em, $user;

        // Create Battle Entity
        $this->_battle             = new Entities\Battle;
        $this->_battle->initiator  = $user->character;
        $this->round               = 1;
        $em->persist($this->_battle);

        // Create and Initialize Battletick-Timer
        $this->initTimer();

        $em->flush();
    }

    /**
     * Remove a Member from the Battle
     * @param Character $char The Character to remove
     * @return bool true if successful, else false
     */
    public function leaveBattle(Character $character)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        // Cycle Token if this is the token-owner
        if ($this->_battle->getTokenOwner() === $this->_battle->getMember($character)) {
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
     * Retrieve the Messages from the Database
     * @return \Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMessage
     */
    public function getResultMessages()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        return $this->_battle->getAllMessages();
    }

    /**
     * Pass the Token to a new Battle Member
     * @return bool true if successful, else false
     */
    public function cycleToken()
    {
        $oldOwner = $this->_battle->getTokenOwner();

        $activemembers = $this->_battle->getAllMembers();

        foreach ($activemembers as $member) {
            if ($member !== $oldOwner) {
                $this->_battle->setTokenOwner($member->character);
                return true;
            }
        }

        if ($this->_battle->getTokenOwner() === $oldOwner) {
            // Still old owner
            return false;
        }
    }

    /**
     * Get List of all Battle Members (excluding Neutrals if $side isn't set)
     * @param bool|string $side Get List of a specific Side
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
        return $this->_getBattleMemberList(\Main\Entities\BattleMember::SIDE_ATTACKERS);
    }

    /**
     * Get List of Neutrals
     * @return array Array of Neutrals
     */
    public function getNeutralList()
    {
        return $this->_getBattleMemberList(\Main\Entities\BattleMember::SIDE_NEUTRALS);
    }

    /**
     * Get List of Defenders
     * @return array Array of Defenders
     */
    public function getDefenderList()
    {
        return $this->_getBattleMemberList(\Main\Entities\BattleMember::SIDE_DEFENDERS);
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

        foreach ($this->_battle->getAllMembers() as $member) {
            if ($member->character->healthpoints <= 0) {
                $result[] = $member;
            }
        }

        return $result;
    }

    /**
     * Returns skillchooser Form
     * @param bool $directoutput Output via OutputObject
     * @return string HTML-Code
     */
    public function showSkillChooser(Character $character, $directoutput=true)
    {
        global $user;
        $output			= "";
        $outputObject 	= Manager\System::getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();
        $member 	= $this->_battle->getMember($user->character);

        if ($member->isNeutral()) {
            // Caller is Neutral
            $output .= "Beobachte den Kampf...";
        } elseif ($member->hasMadeAnAction()) {
            // Caller made his Action
            $output .= "Warte auf andere Kämpfer...";
        } else {
            // Show the Skillchooser
            $skillForm = $outputObject->addForm("skillchooser");

            $skillForm->head("skillchooser", $outputObject->url->base."&{$battleopstr}=use_skill");

            // Add Nav
            $outputObject->nav->addHiddenLink($outputObject->url->base."&{$battleopstr}=use_skill");

            // TODO: Get Available Skills for this Character
            $skills = array ( "Heilen" );

            $skillForm->setCSS("input");
            $skillForm->selectStart("skill");
            foreach ($skills as $skill) {
                $tempskill = Manager\Battle::getSkill($skill);
                $skillForm->selectOption($tempskill->getName(), $tempskill->getName(), false, $tempskill->getDescription());
            }
            $skillForm->selectEnd();

            $skillForm->selectStart("target");
            $skillForm->selectEnd();

            $skillForm->submitButton("Ausführen");
            $skillForm->close();
            $output .= "<span id='skilldescription' class='floatclear'></span>";

            // Target-Chooser
            // The third Parameter is the name of the select-Form where we choose the skill
            // The fourth Parameter is the name of the select-Form where the targets appear
            $outputObject->addJavaScript("$(function(){
                                        getTargetList(".$this->_battle->id.", ".$user->character->id.", 'skill', 'target');
            });");
        }

        if ($directoutput) {
            $outputObject->output($output, true);
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
        $outputobject 	= Manager\System::getOutputObject();

        $output = "";

        foreach (array(\Main\Entities\BattleMember::SIDE_ATTACKERS=>"Angreifer", \Main\Entities\BattleMember::SIDE_DEFENDERS=>"Verteidiger") as $sysname=>$realname) {
            $output .= "`n$realname: `n";

            $temparray = array();
            foreach ($this->getMemberList($sysname) as $member) {

                if ($member->hasMadeAnAction()) {
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
        $outputobject 	= Manager\System::getOutputObject();

        $output 	= "";

        $beforeSS	= $this->battlemembersnapshot;
        $afterSS	= $this->_getBattleMemberSnapshot();

        if (!is_array($beforeSS) || !is_array($afterSS)) {
//			return $output;
        }

        foreach ($beforeSS['data'] as $memberid => $memberdata) {
            $output .= Manager\User::getCharacterName($memberid, true) . ": `n";

            $member	= $this->_battle->getMember($memberid);
            $status = "";

            switch ($member->status) {
                case Entities\BattleMember::STATUS_ACTIVE: $status = "Aktiv"; break;
                case Entities\BattleMember::STATUS_INACTIVE: $status = "Inaktiv"; break;
                case Entities\BattleMember::STATUS_EXCLUDED: $status = "Ausgeschlossen"; break;
                case Entities\BattleMember::STATUS_BEATEN: $status = "Tot"; break;
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
        $outputobject 	= Manager\System::getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->addHead("Kampf")
                          ->addLink("Fliehen", $outputobject->url->base."&{$battleopstr}=flee");
    }

    /**
     * Add Battle Navigation (before Battle)
     */
    public function addCreateBattleNav()
    {
        $outputobject 	= Manager\System::getOutputObject();
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->addHead("Kampf")
                          ->addLink("Anfangen", $outputobject->url->base."&{$battleopstr}=create");
    }

    /**
     * Add Battle Navigation (in Battle)
     */
    public function addAdminBattleNav()
    {
        $outputobject 	= Manager\System::getOutputObject();
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
     * @return array Array of Main\Entities\BattleMember
     */
    private function _getBattleMemberList($side=false, array $status=array())
    {
        $result = array();

        foreach ($this->_battle->getAllMembers() as $member) {
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
                $result = $this->_battle->getAllMembers();
            }
        }

        return $result;
    }

    /**
     * Choose Skill to execute
     * @param Character $char
     * @param mixed $target
     * @param Main\Controller\SkillBase $action The Skill to use
     * @return bool true if successful, else false
     */
    public function chooseSkill(Character $character, $target, \Main\Controller\SkillBase $skill)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        // Check if the Char is Part of this Battle
        $member = $this->_battle->getMember($character);

        // Add the Action to the battletable
        $member->setAction($target, $skill);

        // Mark Member as Active
        $member->setActive();
    }

    /**
     * Check for beaten Members and move them to Neutrals with the beaten-flag given
     */
    public function checkBeatenMembers()
    {
        $beatenlist = $this->getBeatenList();

        foreach ($beatenlist as $member) {
            // Member is beaten
            $this->_battle->addMessage($member->displayname . " wurde besiegt!");

            // Set status to beaten
            $member->setBeaten();

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
        if (count($this->getMemberList(\Main\Entities\BattleMember::SIDE_ATTACKERS)) < 1
            || count($this->getMemberList(\Main\Entities\BattleMember::SIDE_DEFENDERS)) < 1) {
            return false;
        }

        // Check if the Timer ran out
        if ( !($this->getTimer()) ) {
            return true;
        }

        if (count($this->_battle->getActionNeededList()) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Return the Battle Table, sorted by the Characterspeed (fastest first)
     * @return array Battle Table
     */
    private function _getSortedBattleTableByCharacterSpeed()
    {
        global $em;

        // This is easier with an Query than in PHP
        $qb = $em->createQueryBuilder();

        $result = $qb   ->select("ba")
                        ->from("Main:BattleAction", "ba")
                        ->join("ba.initiator", "bm")
                        ->where("ba.battle = ?1")->setParameter(1, $this->_battle)
                        ->andWhere("bm.status = ?2")
                        ->setParameter(2, Entities\BattleMember::STATUS_ACTIVE)
                        ->orWhere("bm.status = ?3")
                        ->setParameter(3, Entities\BattleMember::STATUS_INACTIVE)
                        ->orderBy("bm.speed", "DESC")
                        ->addOrderBy("bm.side", "ASC") // attackers before defenders if speed is equal
                        ->getQuery()->getResult();
/*
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
*/
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
        global $user, $em;

        $battleop 		= $this->_getBattleOpString();
        $outputobject 	= Manager\System::getOutputObject();

        if ($battle= Manager\Battle::getBattle($user->character)) {
            // Load the Battle
            $this->_battle = $battle;

            // Battle JavaScript
            $outputobject->addJavaScriptFile("jquery.plugin.timers.js");
            $outputobject->addJavaScriptFile("battle.func.js");

            // Add Autorefresher + Statuschecker
            if ($this->_battle->getTokenOwner() == $this->_battle->getMember($user->character)) {
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
                if ($this->_battle->getTokenOwner() == $this->_battle->getMember($user->character)) {
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
                    if (!$this->_battle) {
                        $this->_battle = $em->find("Main:Battle", $_GET['battleid']);
                    }
                    $this->_battle->addMember($user->character, $_GET['side']);
                }

                $outputobject->refresh(true);
                break;

            case "part":
                // Part a Battle
                $this->leaveBattle($user->character);

                $outputobject->refresh(true);
                break;

            case "flee":
                // Part a Battle
                $this->leaveBattle($user->character);

                $outputobject->refresh(true);
                break;

            case "create":
                // Create a new Battle
                $this->initialize();

                // Automatically add the Creator to the attackers and give him the token
                $this->_battle->addMember($user->character, \Main\Entities\BattleMember::SIDE_ATTACKERS);
                $this->_battle->setTokenOwner($user->character);

                $outputobject->refresh(true);
                break;

            case "use_skill":
                if (isset($_POST['skill']) && isset($_POST['target'])) {
                    $this->chooseSkill($user->character, $_POST['target'], Manager\Battle::getSkill($_POST['skill']));
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
                $this->_battle->clearMessages();

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

        if ($battleMembers) {
            // Set the default action for the rest of the users
            foreach ($battleMembers as $member) {

                if ($member->isActive()) {
                    // First inactive Round
                    $this->chooseSkill($member, "none", ModuleSystem::getSkillModule("wait"));
                    $member->setInactive();
                } elseif ($member->isInactive()) {
                    // Second inactive Round => exclude Battlemember
                    $this->_battle->addMessage($member->displayname . " wurde wegen Inaktivität vom Kampf ausgeschlossen.");
                    $member->setExcluded();
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

        foreach ($this->getMemberList() as $member) {

            $ssdata['data'][$member->id] = array();

            foreach ($ssfields as $property) {
                $ssdata['data'][$member->id][$property] = $member->{$property};
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
     * Start the Battle
     */
    public function startBattle()
    {
        if (!$this->_battle->active) {
            // Create Battle Member Snapshot
            $this->_writeBattleMemberSnapshot();

            // Start Timer
            $this->startTimer();

            // Set Active-Flag
            $this->_battle->active = true;
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
        $fightmembers = $this->_battle->getAllActiveMembers();

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
            $skill = Manager\Battle::getSkill($action->skill->name);

            // Prepare the Skill
            $skill->prepare($this, $action);

            // Activate the Skill
            $skill->activate();

            // Cleanup Skillaction (saves changed Characters, etc)
            $skill->finish();

            // Save Result Message
            foreach ($skill->getMessages() as $message) {
                $this->_battle->addMessage($message);
            }
        }

        // Check for beaten Characters
        $this->checkBeatenMembers();

        // Reset Battletimer
        $this->resetTimer();

        // Reset Battle Table
        $this->resetBattleTable();

        // Increase Round Number
        $this->_battle->round = $this->_battle->round+1;
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
     * Reset Battle Table
     */
    public function resetBattleTable()
    {
        global $em;

        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        foreach ($this->_battle->actions as $action) {
            $em->remove($action);
        }
    }

    /**
     * Retrieve Battle Round Nr.
     * @return int
     */
    public function getRound()
    {
        return $this->_battle->round;
    }
}
?>
