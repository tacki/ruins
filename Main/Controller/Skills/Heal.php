<?php
/**
 * Heal Skill
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

namespace Main\Controller\Skills;
use Main\Controller\Dice;

class Heal extends \Main\Controller\SkillBase implements \Common\Interfaces\Skill
{
    public function getName() { return "Heilen"; }

    public function getDescription() { return "Etwas Glitter über die Wunden und gut is!"; }

    public function getType() { return parent::TYPE_DEFENSIVE; }

    public function getNrOfTargets() { return 1; }

    public function getPossibleTargets() { return parent::POSSIBLE_TARGET_ALLIES; }

    public function activate()
    {
        foreach ($this->targets as $target) {
            // heal target for 1-4 hp
            $hp = Dice::RollD4();
            $target->character->healthpoints += $hp;

            if ($target->character->healthpoints >= $target->character->lifepoints) {
                $target->character->healthpoints = (int)$target->character->lifepoints;
            }

            if ($this->initiator === $target) {
                $this->addMessage($this->initiator->character->displayname . " heilt sich selbst um " . $hp . " Lebenspunkte");
            } else {
                $this->addMessage($this->initiator->character->displayname . " heilt " . $target->character->displayname . " um " . $hp . " Lebenspunkte");
            }
        }

    }
}