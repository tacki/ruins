<?php
/**
 * Human Race Class
 *
 * Human Race Class
 * @author Markus Schlegel <g42@gmx.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: human.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Human Race Class
 *
 * Human Race Class
 * @package Ruins
 */
class Human extends Race
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "Human Race Class"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Human Race Class"; }

    /**
     * Return the Racename Shortcut, found at the Database
     */
    public function getShortName()
    {
        return "human";
    }

    /**
     * Return the Human Readable Form of the Racename
     */
    public function getHumanReadable()
    {
        if ($this->character->sex == "male") {
            // Male
            return "Mensch";
        } elseif ($this->character->sex == "female") {
            // Female
            return "Mensch";
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
            // From D20: 4' 5" + 2D10
            // metric: 	4*30cm + 5*2.5cm + 2D10 * 2.5cm
            $addSize = Dice::rollD10(2) * 2.5;
            $totalSize = round(120 + 12.5 + $addSize);
            return $totalSize;
        } else {
            // Male
            // From D20: 4' 10" + 2D10
            // metric: 	4*30cm + 10*2.5cm + 2D10 * 2.5cm
            $addSize = Dice::rollD10(2) * 2.5;
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
            // From D20: 85lb + 2D10 * 2D4
            // metric: 	38,5kg + (2D10 * 2D4 * 0.45kg)
            // We won't use 2D10 here, cause we don't know the Heightmodificator
            // 1D10 gives a more 'normal' Weight
            $addWeight = Dice::rollD10() * Dice::rollD4(2) * 0.45;
            $totalWeight = round(38.5 + $addWeight);
            return $totalWeight;
        } else {
            // Male
            // From D20: 120lb + 2D10 * 2D4
            // metric: 	54,4kg + (2D10 * 2D4 * 0.45kg)
            // We won't use 2D10 here, cause we don't know the Heightmodificator
            // 1D10 gives a more 'normal' Weight
            $addWeight = Dice::rollD10() * Dice::rollD10(2) * 0.45;
            $totalWeight = round(54.4 + $addWeight);
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
                return 15 + Dice::RollD4();
            case "bard":
            case "fighter":
            case "paladin":
            case "ranger":
                return 15 + Dice::rollD6();
            case "cleric":
            case "druid":
            case "monk":
            case "wizard":
                return 15 + Dice::rollD6(2);
        }
    }

    /**
     * Generate Max Age
     */
    public function generateMaxAge()
    {
        return 70 + Dice::rollD20(2);
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
