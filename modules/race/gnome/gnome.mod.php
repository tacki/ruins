<?php
/**
 * Gnome Race Class
 *
 * Gnome Race Class
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: gnome.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Gnome Race Class
 *
 * Gnome Race Class
 * @package Ruins
 */
class Gnome extends Race
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName()	{ return "Gnome Race Class"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Gnome Race Class";	}

    /**
     * Return the Racename Shortcut, found at the Database
     */
    public function getShortName()
    {
        return "gnome";
    }

    /**
     * Return the Human Readable Form of the Racename
     */
    public function getHumanReadable()
    {
        if ($this->character->sex == "male") {
            // Male
            return "Gnom";
        } elseif ($this->character->sex == "female") {
            // Female
            return "Gnom";
        } else {
            return "Unbekannt";
        }
    }

    /**
     * Generate Height (metric system)
     */
    public function generateHeight()
    {
        if ($this->character->sex) {
            // Female
            // From D20: 2' 10" + 2D4
            // metric: 	2*30cm + 10*2.5cm + 2D4 * 2.5cm
            $addSize = Dice::rollD4(2) * 2.5;
            $totalSize = round(60 + 25 + $addSize);
            return $totalSize;
        } else {
            // Male
            // From D20: 3' 0" + 2D4
            // metric: 	3*30cm + 0*2.5cm + 2D4 * 2.5cm
            $addSize = Dice::rollD4(2) * 2.5;
            $totalSize = round(90 + 0 + $addSize);
            return $totalSize;
        }
    }

    /**
     * Generate Weight
     */
    public function generateWeight()
    {
        if ($this->character->sex) {
            // Female
            // From D20: 35lb + 2D4 * 1
            // metric: 	15,8kg + 2D4 * 0.45kg
            $addWeight = Dice::rollD4(2) * 0.45;
            $totalWeight = round(15.8 + $addWeight);
            return $totalWeight;
        } else {
            // Male
            // From D20: 40lb + 2D4 * 1
            // metric: 	18,1kg + 2D4 * 0.45kg
            $addWeight = Dice::rollD4(2) * 0.45;
            $totalWeight = round(18.1 + 0.45);
            return $totalWeight;
        }
    }

    /**
     * Generate Age
     */
    public function generateAge()
    {
        switch ($this->character->profession) {
            case "barbarian":
            case "rogue":
            case "sorcerer":
                return 40 + Dice::RollD6(4);
            case "bard":
            case "fighter":
            case "paladin":
            case "ranger":
                return 40 + Dice::rollD6(6);
            case "cleric":
            case "druid":
            case "monk":
            case "wizard":
                return 40 + Dice::rollD6(9);
        }
    }

    /**
     * Generate Max Age
     */
    public function generateMaxAge()
    {
        return 200 + Dice::rollD100(3);
    }

    /**
     * Get Base Speed
     */
    public function getBaseSpeed()
    {
        return 20;
    }
}
?>
