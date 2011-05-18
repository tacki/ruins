<?php
/**
 * HalfElf Race Class
 *
 * HalfElf Race Class
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: halfelf.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * HalfElf Race Class
 *
 * HalfElf Race Class
 * @package Ruins
 */
class Halfelf extends Race
{

    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName()	{ return "Halfelf Race Class"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Halfelf Race Class"; }

    /**
     * Return the Racename Shortcut, found at the Database
     */
    public function getShortName()
    {
        return "halfelf";
    }

    /**
     * Return the Human Readable Form of the Racename
     */
    public function getHumanReadable()
    {
        if ($this->character->sex == "male") {
            // Male
            return "Halb-Elf";
        } elseif ($this->character->sex == "female") {
            // Female
            return "Halb-Elfe";
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
            // From D20: 4' 5" + 2D8
            // metric: 	4*30cm + 5*2.5cm + 2D8 * 2.5cm
            $addSize = Dice::rollD8(2) * 2.5;
            $totalSize = round(120 + 12.5 + $addSize);
            return $totalSize;
        } else {
            // Male
            // From D20: 4' 7" + 2D8
            // metric: 	4*30cm + 7*2.5cm + 2D8 * 2.5cm
            $addSize = Dice::rollD8(2) * 2.5;
            $totalSize = round(90 + 17.5 + $addSize);
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
            // From D20: 80lb + 2D8 * 2D4
            // metric: 	45,3kg + (2D8 * 2D4 * 0.45kg)
            // We won't use 2D8 here, cause we don't know the Heightmodificator
            // 1D8 gives a more 'normal' Weight
            $addWeight = Dice::rollD8() * Dice::rollD4(2) * 0.45;
            $totalWeight = round(36.2 + $addWeight);
            return $totalWeight;
        } else {
            // Male
            // From D20: 100lb + 2D8 * 2D4
            // metric: 	58,9kg + (2D8 * 2D4 * 0.45kg)
            // We won't use 2D8 here, cause we don't know the Heightmodificator
            // 1D8 gives a more 'normal' Weight
            $addWeight = Dice::rollD8() * Dice::rollD4(2) * 0.45;
            $totalWeight = round(45.3 + $addWeight);
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
                return 20 + Dice::RollD6(1);
            case "bard":
            case "fighter":
            case "paladin":
            case "ranger":
                return 20 + Dice::rollD6(2);
            case "cleric":
            case "druid":
            case "monk":
            case "wizard":
                return 20 + Dice::rollD6(3);
        }
    }

    /**
     * Generate Max Age
     */
    public function generateMaxAge()
    {
        return 125 + Dice::rollD20(3);
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
