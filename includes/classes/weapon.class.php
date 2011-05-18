<?php
/**
 * Weapon Class
 *
 * Base Weaopn-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: weapon.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Weapon Class
 *
 * Base Weaopn-Class
 * @package Ruins
 */
class Weapon extends ItemOverload
{
    /**
     * Calculate the Minimum Damage of this Weapon
     * @return int Minimum Damage (buff included)
     */
    public function getMinDamage()
    {
        if ($this->buff) {
            $result = 	$this->weapondamage_min +
                        $this->buff->weapondamage_min_mod +
                        $this->buff->weapondamage_mod;
        } else {
            $result = $this->weapondamage_min;
        }

        return $result;
    }

    /**
     * Calculate the Maximum Damage of this Weapon
     * @return int Maximum Damage (buff included)
     */
    public function getMaxDamage()
    {
        if ($this->buff) {
            $result = 	$this->weapondamage_max +
                        $this->buff->weapondamage_max_mod +
                        $this->buff->weapondamage_mod;
        } else {
            $result = $this->weapondamage_max;
        }

        return $result;
    }

    /**
     * Generate a 'Min - Max' Damage Output
     * @param bool $output Set to true if you want to output it directly
     * @return string HTML-formed Output of Min-Max Damage
     */
    public function showDamage($output=false)
    {
        $showclass = "itemstat";
        $improved  = false;
        $decreased = false;
        $mindamage = $this->getMinDamage();
        $maxdamage = $this->getMaxDamage();

        // Improvements are dominant
        if ($mindamage > $this->weapondamage_min || $maxdamage > $this->weapondamage_max) {
            $improved = true;
        } elseif ($mindamage < $this->weapondamage_min || $maxdamage < $this->weapondamage_max) {
            $decreased = true;
        }

        if ($improved) {
            $showclass = "itemstat_improved";
        } elseif ($decreased) {
            $showclass = "itemstat_decreased";
        }

        $outputText = "<span class='" . $showclass . "'>";
        $outputText .= $mindamage . " - " . $maxdamage;
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
