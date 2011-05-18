<?php
/**
 * The incredible 'do nothing'-Skill
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: wait.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * The incredible 'do nothing'-Skill
 *
 * @package Ruins
 */
class Wait extends Skill
{
    public $name 		= "Warten";
    public $description	= "Abwarten und nichts tun";
    public $type 		= "none";

    /**
     * @see Skill::activate()
     */
    public function activate()
    {
        $this->addResultMessage($this->initiator->displayname . " wartet ab und sieht zu.");
    }
}
?>
