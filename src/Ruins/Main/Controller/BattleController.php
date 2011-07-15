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
namespace Ruins\Main\Controller;
use Ruins\Common\Controller\SessionStore;
use Ruins\Common\Controller\Error;
use Ruins\Main\Controller\SkillBase;
use Ruins\Main\Controller\TimerController as Timer;
use Ruins\Main\Entities\Battle;
use Ruins\Main\Entities\Character;
use Ruins\Main\Entities\BattleMember;
use Ruins\Common\Controller\Registry;


/**
 * Battle Class
 *
 * @package Ruins
 */
class BattleController extends Controller
{
    /**
     * Battle Object
     * @var Ruins\Main\Entities\Battle
     */
    private $_battle;

    /**
     * Battle Timer Controlling Object
     * @var Ruins\Main\Repositories\TimerRepository
     */
    private $_battleTimerControl = NULL;

    /**
     * constructor - load the default values and initialize the attributes
     */
    public function __construct()
    {
        $em = Registry::getEntityManager();

        // Set default Repository
        $this->setRepository($em->getRepository("Main:Battle"));

        // Add the Helper Functions
        // Only if we have an OutputObject
        if (Registry::get('main.output')) {
            $this->_addHelper();
        }
    }

    /**
     * Initialize for a new Battle
     */
    public function initialize()
    {
        $em = Registry::getEntityManager();
        $user = Registry::getUser();

        // Create Battle Entity
        $battle = $this->getRepository()->create();
        $this->load($battle, $user->character);

        if ($this->_battle) {
            // Set Battle Initiator
            $this->_battle->initiator  = $user->character;

            // Init Timer
            $this->initTimer();
        }
    }

    /**
     * Enter description here ...
     * @param Ruins\Main\Entities\Battle $battle
     */
    public function load(Battle $battle)
    {
        // Set Class Object
        $this->_battle = $battle;

        // Set Reference
        $this->getRepository()->setReference($this->_battle);
    }

    public function getEntity()
    {
        return $this->_battle;
    }

    /**
     * Remove a Member from the Battle
     * @param Character $char The Character to remove
     * @return bool true if successful, else false
     */
    public function leaveBattle(Character $character)
    {
        $em = Registry::getEntityManager();

        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        // Cycle Token if this is the token-owner
        if ($this->getRepository()->getTokenOwner() === $this->getRepository()->getBattleMember($character)) {
            // Give the Token to another Battle Member
            $this->cycleToken();
        }

        // Remove Character from Battle
        $this->getRepository()->removeCharacterFromBattle($character);

        // Check if there are enough Battle Members to continue
        if ( $this->getRepository()->getAllAttackers()->isEmpty()
            || $this->getRepository()->getAllDefenders()->isEmpty() ) {
            $this->finish();
        }

        return $result;
    }

    /**
     * Completly remove a Battle and all Members/Messages
     */
    public function removeBattle()
    {
        $em = Registry::getEntityManager();

        if ($this->_battle) {
            $em->remove($this->_battle);
        }

        $this->_battle = NULL;
    }

    /**
     * Retrieve the Messages from the Database
     * @return \Doctrine\Common\Collections\ArrayCollection Array of Ruins\Main\Entities\BattleMessage
     */
    public function getResultMessages()
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        return $this->getRepository()->getAllMessages();
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

    public function showResultStats($directoutput=true)
    {
        $em = Registry::getEntityManager();

        $outputobject 	= Registry::get('main.output');

        $output 	= "";

        $beforeSS	= $this->battlemembersnapshot;
        $afterSS	= $this->_getBattleMemberSnapshot();

        if (!is_array($beforeSS) || !is_array($afterSS)) {
//			return $output;
        }

        foreach ($beforeSS['data'] as $memberid => $memberdata) {
            $character = $em->find("Main:Character", $memberid);
            $output .= $character->displayname . ": `n";

            $member	= $this->getRepository()->getBattleMember($character);

            switch ($member->status) {
                default: $status = ""; break;
                case BattleMember::STATUS_ACTIVE: $status = "Aktiv"; break;
                case BattleMember::STATUS_INACTIVE: $status = "Inaktiv"; break;
                case BattleMember::STATUS_EXCLUDED: $status = "Ausgeschlossen"; break;
                case BattleMember::STATUS_BEATEN: $status = "Tot"; break;
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
        $outputobject 	= Registry::get('main.output');
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->addHead("Kampf")
                          ->addLink("Fliehen", $outputobject->url->base."&{$battleopstr}=flee");
    }

    /**
     * Add Battle Navigation (before Battle)
     */
    public function addCreateBattleNav()
    {
        $outputobject 	= Registry::get('main.output');
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->addHead("Kampf")
                          ->addLink("Anfangen", $outputobject->url->base."&{$battleopstr}=create");
    }

    /**
     * Add Battle Navigation (in Battle)
     */
    public function addAdminBattleNav()
    {
        $outputobject 	= Registry::get('main.output');
        $battleopstr 	= $this->_getBattleOpString();

        $outputobject->nav->addHead("Admin")
                          ->addLink("Kampf Beenden", $outputobject->url->base."&{$battleopstr}=admin_remove")
                          ->addLink("Nachrichten Löschen", $outputobject->url->base."&{$battleopstr}=admin_removemessages");
    }

    /**
     * Choose Skill to execute
     * @param Character $char
     * @param mixed $target
     * @param Main\Controller\SkillBase $action The Skill to use
     * @return bool true if successful, else false
     */
    public function chooseSkill(Character $character, $target, SkillBase $skill)
    {
        // Check if this Battle is initialized
        if (!$this->_battle) {
            return false;
        }

        // Check if the Char is Part of this Battle
        $member = $this->getRepository()->getBattleMember($character);

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
        $em = Registry::getEntityManager();

        $beatenlist = $this->getBeatenList();

        foreach ($beatenlist as $member) {
            // Member is beaten
            $this->getRepository()->addMessage($member->displayname . " wurde besiegt!");

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
        if (count($this->getRepository()->getAllMembersAtSide(BattleMember::SIDE_ATTACKERS)) < 1
            || count($this->getRepository()->getAllMembersAtSide(BattleMember::SIDE_DEFENDERS)) < 1) {
            return false;
        }

        // Check if the Timer ran out
        if ( !($this->getTimer()) ) {
            return true;
        }

        if (count($this->getRepository()->getActionNeededList()) > 0) {
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
        $em = Registry::getEntityManager();

        // This is easier with an Query than in PHP
        $qb = $em->createQueryBuilder();

        $result = $qb   ->select("ba")
                        ->from("Main:BattleAction", "ba")
                        ->join("ba.initiator", "bm")
                        ->where("ba.battle = ?1")->setParameter(1, $this->_battle)
                        ->andWhere("bm.status = ?2")
                        ->setParameter(2, BattleMember::STATUS_ACTIVE)
                        ->orWhere("bm.status = ?3")
                        ->setParameter(3, BattleMember::STATUS_INACTIVE)
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
        $user = Registry::getUser();
        $em = Registry::getEntityManager();

        $battleop 		= $this->_getBattleOpString();
        $outputobject 	= Registry::get('main.output');

        if ($battle = $em->getRepository("Main:Character")->getBattle($user->character)) {
            // Load the Battle
            $this->load($battle);

            // Battle JavaScript
            $outputobject->addJavaScriptFile("jquery.plugin.timers.js");
            $outputobject->addJavaScriptFile("battle.func.js");

            // Add Autorefresher + Statuschecker
            if ($this->getRepository()->getTokenOwner() == $this->getRepository()->getBattleMember($user->character)) {
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
                if ($this->_battle->getTokenOwner() == $this->getRepository()->getBattleMember($user->character)) {
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
                    $battlemember = $this->getRepository()->addCharacterToBattle($user->character, $this->_battle);

                    $battlemember->side = $_GET['side'];
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
                $member = $this->getRepository()->addCharacterToBattle($user->character, $this->_battle);
                $member->side = BattleMember::SIDE_ATTACKERS;
                $this->getRepository()->setTokenOwner($user->character);

                $outputobject->refresh(true);
                break;

            case "use_skill":
                if (isset($_POST['skill']) && isset($_POST['target'])) {
                    $this->chooseSkill($user->character, $_POST['target'], $em->getRepository("Main:Skill")->getController($_POST['skill']));
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
        $em = Registry::getEntityManager();

        // First get the Users which didn't make an action
        $battleMembers = $this->getRepository()->getActionNeededList();

        if ($battleMembers) {
            // Set the default action for the rest of the users
            foreach ($battleMembers as $member) {

                if ($member->isActive()) {
                    // First inactive Round
                    $this->chooseSkill($member, "none", ModuleSystem::getSkillModule("wait"));
                    $member->setInactive();
                } elseif ($member->isInactive()) {
                    // Second inactive Round => exclude Battlemember
                    $this->getRepository()->addMessage($member->displayname . " wurde wegen Inaktivität vom Kampf ausgeschlossen.");
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

        foreach ($this->getRepository()->getAllMembers() as $member) {

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
        if (!$this->_battle->isActive()) {
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
        $em = Registry::getEntityManager();

        // Start the Battle if it isn't already running
        $this->startBattle();

        // Finish the Battle if there aren't enough BattleMembers to fight
        $fightmembers = $this->getRepository()->getAllActiveMembers();

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
            // Prepare the Skill
            $action->skill->prepare($this, $action);

            // Activate the Skill
            $action->skill->activate();

            // Cleanup Skillaction
            $action->skill->finish();

            // Save Result Message
            foreach ($skill->getMessages() as $message) {
                $this->getRepository()->addMessage($message);
            }
        }

        // Check for beaten Characters
        $this->checkBeatenMembers();

        // Reset Battletimer
        $this->resetTimer();

        // Reset Battle Table
        $this->getRepository()->clearActions();

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
        if ($this->_battle->isActive()) {
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
        $em = Registry::getEntityManager();

        if (!$this->_battleTimerControl) {
            $this->_battleTimerControl = $em->getRepository("Main:Timer")
                                            ->create($this->_battle->timer->name);
        }

        return $this->_battleTimerControl->get();
    }

    /**
     * Initialize/Set Battletimer
     */
    public function initTimer()
    {
        $em = Registry::getEntityManager();
        $systemConfig = Registry::getMainConfig();

        $this->_battleTimerControl = $em->getRepository("Main:Timer")
                                        ->create(uniqid("battle_"));

        $this->_battle->timer = $this->_battleTimerControl->getEntity();

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
        $em = Registry::getEntityManager();
        $systemConfig = Registry::getMainConfig();

        if (!$this->_battleTimerControl) {
            $this->_battleTimerControl = $em->getRepository("Main:Timer")
                                            ->create($this->_battle->timer->name);
        }

        $this->_battleTimerControl->set($systemConfig->get("battle_roundtime", 120), 0, 0, true);
    }

    /**
     * Start Battletimer
     */
    public function startTimer()
    {
        $em = Registry::getEntityManager();

        if (!$this->_battleTimerControl) {
            $this->_battleTimerControl = $em->getRepository("Main:Timer")
                                            ->create($this->_battle->timer->name);
        }

        $this->_battleTimerControl->start();
    }

    /**
     * Stop Battletimer
     */
    public function stopTimer()
    {
        $em = Registry::getEntityManager();

        if (!$this->_battleTimerControl) {
            $this->_battleTimerControl = $em->getRepository("Main:Timer")
                                            ->create($this->_battle->timer->name);
        }

        $this->_battleTimerControl->stop();
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
