<?php
/**
 * Elven Race Class
 *
 * Elven Race Class
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: elf.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Elven Race Class
 *
 * Elven Race Class
 * @package Ruins
 */
class Elf extends Race
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "Elven Race Class"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Elven Race Class";	}

    /**
     * Return the Racename Shortcut, found at the Database
     */
    public function getShortName()
    {
        return "elf";
    }

    /**
     * Return the Human Readable Form of the Racename
     */
    public function getHumanReadable()
    {
        if ($this->character->sex == "male") {
            // Male
            return "Elf";
        } elseif ($this->character->sex == "female") {
            // Female
            return "Elfe";
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
            // From D20: 4' 5" + 2D6
            // metric: 	4*30cm + 5*2.5cm + 2D6 * 2.5cm
            $addSize = Dice::rollD6(2) * 2.5;
            $totalSize = round(120 + 12.5 + $addSize);
            return $totalSize;
        } else {
            // Male
            // From D20: 4' 5" + 2D6
            // metric: 	4*30cm + 5*2.5cm + 2D6 * 2.5cm
            $addSize = Dice::rollD6(2) * 2.5;
            $totalSize = round(120 + 12.5 + $addSize);
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
            // From D20: 80lb + 2D6 * 1D6
            // metric: 	36,2kg + (2D6 * 1D6 * 0.45kg)
            // We won't use 2D6 here, cause we don't know the Heightmodificator
            // 1D6 gives a more 'normal' Weight
            $addWeight = Dice::rollD6() * Dice::rollD6() * 0.45;
            $totalWeight = round(36.2 + $addWeight);
            return $totalWeight;
        } else {
            // Male
            // From D20: 85lb + 2D6 * 1D6
            // metric: 	38,5kg + (2D6 * 1D6 * 0.45kg)
            // We won't use 2D6 here, cause we don't know the Heightmodificator
            // 1D6 gives a more 'normal' Weight
            $addWeight = Dice::rollD6() * Dice::rollD6() * 0.45;
            $totalWeight = round(38.5 + $addWeight);
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
                return 110 + Dice::RollD6(4);
            case "bard":
            case "fighter":
            case "paladin":
            case "ranger":
                return 110 + Dice::rollD6(6);
            case "cleric":
            case "druid":
            case "monk":
            case "wizard":
                return 110 + Dice::rollD6(10);
        }
    }

    /**
     * Generate Max Age
     */
    public function generateMaxAge()
    {
        return 350 + Dice::rollD100(4);
    }

    /**
     * Get Base Speed
     */
    public function getBaseSpeed()
    {
        return 30;
    }
}
?>
