<?php
/**
 * Skill Base Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Main\Controller;
use Ruins\Common\Controller\Registry;
use Ruins\Main\Entities\Skill;
use Ruins\Main\Controller\BattleController;
use Ruins\Main\Entities\BattleAction;

/**
 * Skill Base Class
 *
 * @package Ruins
 */
abstract class SkillBase
{
    const TYPE_DEFENSIVE          = "defensive";
    const TYPE_NEUTRAL            = "neutral";
    const TYPE_OFFENSIVE          = "offensive";

    const POSSIBLE_TARGET_OWN     = "own";
    const POSSIBLE_TARGET_ALLIES  = "allies";
    const POSSIBLE_TARGET_ENEMIES = "enemies";

    /**
     * Module Entity
     * @var Ruins\Main\Entities\Skill
     */
    private $entity;

    /**
     * Array of Targets
     * @var array
     */
    protected $targets = array();

    /**
     * Skill Initiator
     * @var Ruins\Main\Entities\BattleMember
     */
    protected $initiator;

    /**
     * Battle Controller
     * @var Ruins\Main\Controller\BattleController
     */
    protected $battle;

    /**
     * Skill Result Messages
     * @var array
     */
    private $_messages = array();

    /**
     * Module Initialization
     */
    public function init()
    {
        $em = Registry::getEntityManager();

        $skill                  = new Skill;
        $skill->classname       = get_called_class();
        $skill->name            = static::getName();
        $skill->description     = static::getDescription();
        $skill->type            = static::getType();
        $skill->nrOfTargets     = static::getNrOfTargets();
        $skill->possibleTargets = static::getPossibleTargets();

        $em->persist($skill);

        $this->entity = $skill;
    }

    /**
    * Return associated Entity
    * @return \Main\Entities\Skill Entity
    */
    public function getEntity()
    {
        $em = Registry::getEntityManager();

        if (isset($this->entity)) {
            return $this->entity;
        } else {
            $result = $em->getRepository("Main:Skill")->findOneByName(static::getName());
            if ($result) {
                return $result;
            } else {
                throw Error("Entity for Skill ". static::getName() . " not found!");
            }
        }
    }

    /**
     * Get Name of Skill
     * @return string
     */
    public function getName() { return "Undefined"; }

    /**
     * Get Description of Skill
     * @return string
     */
    public function getDescription() { return "Undefined"; }

    /**
     * Get Type of Skill
     * @return string
     */
    public function getType() { return self::TYPE_NEUTRAL; }

    /**
     * Get Number or Targets
     * return int
     */
    public function getNrOfTargets() { return 1; }

    /**
     * Get PossibleTargets
     * return string
     */
    public function getPossibleTargets() { return parent::POSSIBLE_TARGET_OWN; }

    /**
     * Add Result Message
     * @param string $message
     */
    public function addMessage($message)
    {
        $this->_messages[] = $message;
    }

    /**
     * Get Result Messages
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Prepare the skill
     * @param Ruins\Main\Controller\BattleController $battle Battle Controller
     * @param Ruins\Main\Entities\BattleAction $action Battle Action
     */
    public function prepare(BattleController $battle, BattleAction $action)
    {
        $em = Registry::getEntityManager();

        // Set Battle
        $this->battle    = $battle;

        // Set Initiator
        $this->initiator = $action->initiator;

        // Resolve Targets
        foreach ($action->targets as $targetId) {
            $this->targets[] = $em->find("Main:BattleMember", $targetId);
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
    }


}