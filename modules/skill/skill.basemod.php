<?php
/**
 * Skills Base Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id$
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Class defines
 */
define("SKILL_POSSIBLE_TARGET_OWN", 1);
define("SKILL_POSSIBLE_TARGET_ALLIES", 2);
define("SKILL_POSSIBLE_TARGET_ENEMIES", 3);

/**
 * Skills Base Class
 *
 * Base Object for all skills
 * @package Ruins
 */
abstract class Skill extends Module
{
    /**
     * Skillname
     * @var string
     */
    public $name = "";

    /**
     * Description
     * @var string
     */
    public $description;

    /**
     * Skill Type
     * @var define Skill Type Define
     */
    public $type;

    /**
     * Number of Targets
     * @var int
     */
    public $nroftargets = 0;

    /**
     * Possible Target
     * @var int
     */
    public $possibletargets = SKILL_POSSIBLE_TARGET_OWN;

    /**
     * Result Message
     * @var string
     */
    public $resultmessages = array();

    /**
     * Array of Targets
     * @var array
     */
    protected $targets = array();

    /**
     * Battle Object
     * @var Battle
     */
    protected $battle;

    /**
     * Skill Initiator
     */
    protected $initiator;

    /**
     * Prepared Flag
     */
    protected $prepared = false;

    /**
     * Set Battle Environment
     * @param Battle $battle Associated Battle
     */
    public function setBattleEnvironment(&$battle)
    {
        $this->battle = $battle;
    }

    /**
     * Add a Result Message
     */
    public function addResultMessage($message)
    {
        $this->resultmessages[] = $message;
    }

     /**
     * Prepare the skill
     * @param array $action
     */
    public function prepare(array $action)
    {
        global $user;

        // Set Initiator
        $chartype = UserSystem::getCharacterType($action['initiatorid']);
        $this->initiator = new $chartype;
        $this->initiator->load($action['initiatorid']);

        // Set Targets
        if (is_numeric($action['target'])) {
            $this->targets[] = $action['target'];
        } elseif ($action['target'] == 'attackers') {
            $this->targets = $this->battle->getAttackerList();
        } elseif ($action['target'] == 'defenders') {
            $this->targets = $this->battle->getDefenderList();
        } elseif ($action['target'] == 'neutrals') {
            $this->targets = $this->battle->getNeutralList();
        } elseif ($action['target'] == 'none') {
            $this->targets = array();
        }

        foreach ($this->targets as &$targetid) {
            if ($user instanceof User && $user->char instanceof Character && $user->char->id == $targetid) {
                $targetid = $user->char;
            } else {
                $chartype = UserSystem::getCharacterType($targetid);
                $target = new $chartype;
                $target->load($targetid);

                $targetid = $target;
            }
        }

        $this->prepared = true;
    }

    /**
     * Activate the skill
     */
    abstract public function activate();

    /**
     * Finish the Skill
     */
    public function finish()
    {
        if ($this->prepared) {
            foreach ($this->targets as $target) {
                $target->save();
            }
        }
    }

}

?>
