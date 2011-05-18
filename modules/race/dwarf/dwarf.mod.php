<?php
/**
 * Dwarven Race Class
 *
 * Dwarven Race Class
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: dwarf.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Dwarven Race Class
 *
 * Dwarven Race Class
 * @package Ruins
 */
class Dwarf extends Race
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "Dwarf Race Class"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Dwarf Race Class";	}

    /**
     * Return the Racename Shortcut, found at the Database
     */
    public function getShortName()
    {
        return "dwarf";
    }

    /**
     * Return the Human Readable Form of the Racename
     */
    public function getHumanReadable()
    {
        if ($this->character->sex == "male") {
            // Male
            return "Zwerg";
        } elseif ($this->character->sex == "female") {
            // Female
            return "Zwergin";
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
            // From D20: 3' 7" + 2D4
            // metric: 	3*30cm + 7*2.5cm + 2D4 * 2.5cm
            $addSize = Dice::rollD4(2) * 2.5;
            $totalSize = round(90 + 17.5 + $addSize);
            return $totalSize;
        } else {
            // Male
            // From D20: 3' 9" + 2D4
            // metric: 	3*30cm + 9*2.5cm + 2D4 * 2.5cm
            $addSize = Dice::rollD4(2) * 2.5;
            $totalSize = round(90 + 22.5 + $addSize);
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
            // From D20: 100lb + 2D4 * 2D6
            // metric: 	45,3kg + (2D4 * 2D6 * 0.45kg)
            // We won't use 2D4 here, cause we don't know the Heightmodificator
            // 1D4 gives a more 'normal' Weight
            $addWeight = Dice::rollD4() * Dice::rollD6(2) * 0.45;
            $totalWeight = round(45.3 + $addWeight);
            return $totalWeight;
        } else {
            // Male
            // From D20: 130lb + 2D4 * 2D6
            // metric: 	58,9kg + (2D4 * 2D6 * 0.45kg)
            // We won't use 2D4 here, cause we don't know the Heightmodificator
            // 1D4 gives a more 'normal' Weight
            $addWeight = Dice::rollD4() * Dice::rollD6(2) * 0.45;
            $totalWeight = round(58.9 + $addWeight);
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
                return 40 + Dice::RollD6(3);
            case "bard":
            case "fighter":
            case "paladin":
            case "ranger":
                return 40 + Dice::rollD6(5);
            case "cleric":
            case "druid":
            case "monk":
            case "wizard":
                return 40 + Dice::rollD6(7);
        }
    }

    /**
     * Generate Max Age
     */
    public function generateMaxAge()
    {
        return 250 + Dice::rollD100(2);
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
