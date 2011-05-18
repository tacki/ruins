<?php
/**
 * Halfling Race Class
 *
 * Halfling Race Class
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN$
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Halfling Race Class
 *
 * Halfling Race Class
 * @package Ruins
 */
class Halfling extends Race
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName()	{ return "Halfling Race Class";	}

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Halfling Race Class"; }

    /**
     * Return the Racename Shortcut, found at the Database
     */
    public function getShortName()
    {
        return "halfling";
    }

    /**
     * Return the Human Readable Form of the Racename
     */
    public function getHumanReadable()
    {
        if ($this->character->sex == "male") {
            // Male
            return "Halbling";
        } elseif ($this->character->sex == "female") {
            // Female
            return "Halbling";
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
            // From D20: 2' 6" + 2D4
            // metric: 	2*30cm + 6*2.5cm + 2D4 * 2.5cm
            $addSize = Dice::rollD4(2) * 2.5;
            $totalSize = round(60 + 15 + $addSize);
            return $totalSize;
        } else {
            // Male
            // From D20: 2' 8" + 2D4
            // metric: 	2*30cm + 8*2.5cm + 2D4 * 2.5cm
            $addSize = Dice::rollD4(2) * 2.5;
            $totalSize = round(60 + 20 + $addSize);
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
            // From D20: 25lb + 2D4 * 1
            // metric: 	11,3kg + 2D4 * 0.45kg
            $addWeight = Dice::rollD4(2) * 0.45;
            $totalWeight = round(45.3 + $addWeight);
            return $totalWeight;
        } else {
            // Male
            // From D20: 30lb + 2D4 * 1
            // metric: 	13,6kg + 2D4 * 0.45kg
            $addWeight = Dice::rollD4(2) * 0.45;
            $totalWeight = round(13.6 + $addWeight);
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
                return 20 + Dice::RollD4(2);
            case "bard":
            case "fighter":
            case "paladin":
            case "ranger":
                return 20 + Dice::rollD6(3);
            case "cleric":
            case "druid":
            case "monk":
            case "wizard":
                return 20 + Dice::rollD6(4);
        }
    }

    /**
     * Generate Max Age
     */
    public function generateMaxAge()
    {
        return 100 + Dice::rollD20(5);
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
