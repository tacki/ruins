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
namespace Main\Controller;

/**
 * Skill Base Class
 *
 * @package Ruins
 */
class SkillBase
{
    const TYPE_DEFENSIVE          = "defensive";
    const TYPE_NEUTRAL            = "neutral";
    const TYPE_OFFENSIVE          = "offensive";

    const POSSIBLE_TARGET_OWN     = "own";
    const POSSIBLE_TARGET_ALLIES  = "allies";
    const POSSIBLE_TARGET_ENEMIES = "enemies";

    /**
     * Module Entity
     * @var \Main\Entities\Skill
     */
    private $entity;

    /**
     * Array of Targets
     * @var array
     */
    protected $targets = array();

    /**
     * Skill Initiator
     * @var Main\Entities\BattleMember
     */
    protected $initiator;

    /**
     * Battle Entity
     * @var Main\Entities\Battle
     */
    protected $battle;

    /**
     * Module Initialization
     */
    public function init()
    {
        global $em;

        $skill                  = new \Main\Entities\Skill;
        $skill->classname       = get_called_class();
        $skill->name            = static::getName();
        $skill->description     = static::getDescription();
        $skill->type            = static::getType();
        $skill->nrOfTargets     = static::getNrOfTargets();
        $skill->possibleTargets = static::getPossibleTargets();

        $em->persist($module);

        $this->entity = $module;
    }

    /**
    * Return associated Module Entity
    * @return \Main\Entities\Module Module Entity
    */
    public function getModuleEntity()
    {
        global $em;

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
     * Prepare the skill
     * @param Main\Entities\Battle $battle Battle Entity
     * @param Main\Entities\BattleMember $initiator Skill Initiator
     */
    public function prepare(\Main\Entities\Battle $battle, \Main\Entities\BattleMember $initiator)
    {
        // Set Initiator
        $this->initiator = $initiator;

        // Set Targets
        if (is_numeric($action['target'])) {
            $this->targets[] = $action['target'];
        } elseif ($action['target'] == self::POSSIBLE_TARGET_ALLIES) {
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