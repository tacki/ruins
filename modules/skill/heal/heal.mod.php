<?php
/**
 * Heal Skill
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: heal.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Heal Skill
 *
 * @package Ruins
 */
class Heal extends Skill
{
    public $name 			= "Heilen";
    public $description		= "Etwas Glitter über die Wunden und gut is!";
    public $type 			= "defense";
    public $nroftargets		= 1;
    public $possibletargets	= SKILL_POSSIBLE_TARGET_ALLIES;

    /**
     * @see Skill::activate()
     */
    public function activate()
    {
        foreach ($this->targets as $target) {
            // heal target for 1-4 hp
            $hp						= Dice::RollD4();
            $target->healthpoints 	+= $hp;

            if ($target->healthpoints >= $target->lifepoints) {
                $target->healthpoints = (int)$target->lifepoints;
            }

            if ($this->initiator->name == $target->name) {
                $this->addResultMessage($this->initiator->displayname . " heilt sich selbst um " . $hp . " Lebenspunkte");
            } else {
                $this->addResultMessage($this->initiator->displayname . " heilt " . $target->displayname . " um " . $hp . " Lebenspunkte");
            }
        }
    }
}
?>
