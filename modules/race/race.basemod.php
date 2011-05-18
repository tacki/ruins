<?php
/**
 * Race Module Basemod
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: race.basemod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Race Module Basemod
 *
 * @package Ruins
 */
abstract class Race extends Module
{
    /**
     * Character Reference
     * @var Character
     */
    protected $character;

    /**
     * Set Character Reference
     */
    public function setCharacterReference(Character &$character)
    {
        $this->character = $character;
    }

    /**
     * Return the Racename Shortcut, found at the Database
     */
    public function getShortName()
    {
        return "Undefined";
    }

    /**
     * Return the Human Readable Form of the Racename
     */
    public function getHumanReadable()
    {
        return "Undefined";
    }

    /**
     * Return $this->getHumanReadable()
     */
    public function __toString()
    {
        return $this->getHumanReadable();
    }

    /**
     * Generate Height (metric system)
     */
    public function generateHeight()
    {
        return "Undefined";
    }

    /**
     * Generate Weight
     */
    public function generateWeight()
    {
        return "Undefined";
    }

    /**
     * Generate Age
     */
    public function generateAge()
    {
        return "Undefined";
    }

    /**
     * Generate Max Age
     */
    public function generateMaxAge()
    {
        return "Undefined";
    }
}
?>
