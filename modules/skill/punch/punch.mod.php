<?php
/**
 * Punch Skill
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: punch.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Punch Skill
 *
 * @package Ruins
 */
class Punch extends Skill
{
    public $name 			= "Hieb";
    public $description		= "Ein einfacher Hieb, bevorzugt gegen das Kinn";
    public $type 			= "attack";
    public $nroftargets		= 1;
    public $possibletargets	= SKILL_POSSIBLE_TARGET_ENEMIES;

    /**
     * @see Skill::activate()
     */
    public function activate()
    {
        foreach ($this->targets as $target) {
            // hurt all targets for 1-2 healthpoints
            $dmg					= round(Dice::rollD4()/2);
            $target->healthpoints 	-= $dmg;
            $this->addResultMessage($this->initiator->displayname . " haut " . $target->displayname . " und verusacht " . $dmg . " Schaden");
        }
    }
}
?>
