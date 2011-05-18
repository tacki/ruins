<?php
/**
 * Armor Class
 *
 * Base Armor-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: armor.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Item Class
 *
 * Base Item-Class
 * @package Ruins
 */
class Armor extends ItemOverload
{
    /**
     * Calculate the Armorclass of this Armorpart
     * @return int Armorclass
     */
    public function getArmorClass()
    {
        if ($this->buff) {
            $result = 	$this->armorclass +
                        $this->buff->armorclass_mod;
        } else {
            $result = $this->armorclass;
        }

        return $result;
    }

    /**
     * Generate a Armorclass Output
     * @param bool $output Set to true if you want to output it directly
     * @return string HTML-formed Output of Armorclass
     */
    public function showArmorClass($output=false)
    {
        $modified = false;

        if ($this->getArmorClass() != $this->armorclass) {
            $modified = true;
        }

        $outputText = "<span class='" . ($modified?"weaponstat_modified":"weaponstat") . "'>";
        $outputText .= $this->getArmorClass();
        $outputText .= "</span>";

        if ($output) {
            $outputobject = getOutputObject();
            $outputobject->output($outputText, true);
        } else {
            return $outputText;
        }
    }

}
?>
