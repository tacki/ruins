<?php
/**
 * HalfOrc Race Class
 *
 * HalfOrc Race Class
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: halforc.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * HalfOrc Race Class
 *
 * HalfOrc Race Class
 * @package Ruins
 */
class Halforc extends Race
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName()	{ return "Halforc Race Class"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Halforc Race Class"; }

    /**
     * Return the Racename Shortcut, found at the Database
     */
    public function getShortName()
    {
        return "halforc";
    }

    /**
     * Return the Human Readable Form of the Racename
     */
    public function getHumanReadable()
    {
        if ($this->character->sex == "male") {
            // Male
            return "Halb-Ork";
        } elseif ($this->character->sex == "female") {
            // Female
            return "Halb-Ork";
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
            // From D20: 4' 5" + 2D12
            // metric: 	4*30cm + 5*2.5cm + 2D12 * 2.5cm
            $addSize = Dice::rollD12(2) * 2.5;
            $totalSize = round(120 + 12.5 + $addSize);
            return $totalSize;
        } else {
            // Male
            // From D20: 4' 10" + 2D12
            // metric: 	4*30cm + 10*2.5cm + 2D12 * 2.5cm
            $addSize = Dice::rollD12(2) * 2.5;
            $totalSize = round(120 + 25 + $addSize);
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
            // From D20: 110lb + 2D12 * 2D6
            // metric: 	49,8kg + (2D12 * 2D6 * 0.45kg)
            // We won't use 2D12 here, cause we don't know the Heightmodificator
            // 1D12 gives a more 'normal' Weight
            $addWeight = Dice::rollD12() * Dice::rollD6(2) * 0.45;
            $totalWeight = round(49.8 + $addWeight);
            return $totalWeight;
        } else {
            // Male
            // From D20: 150lb + 2D12 * 2D6
            // metric: 	68,0kg + (2D12 * 2D6 * 0.45kg)
            // We won't use 2D12 here, cause we don't know the Heightmodificator
            // 1D12 gives a more 'normal' Weight
            $addWeight = Dice::rollD12() * Dice::rollD6(2) * 0.45;
            $totalWeight = round(68.0 + $addWeight);
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
                return 14 + Dice::RollD4(1);
            case "bard":
            case "fighter":
            case "paladin":
            case "ranger":
                return 14 + Dice::rollD6(1);
            case "cleric":
            case "druid":
            case "monk":
            case "wizard":
                return 14 + Dice::rollD6(2);
        }
    }

    /**
     * Generate Max Age
     */
    public function generateMaxAge()
    {
        return 60 + Dice::rollD10(2);
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
