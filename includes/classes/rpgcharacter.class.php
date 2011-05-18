<?php
/**
 * RPG Character Class
 *
 * Roleplay Character-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: rpgcharacter.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * RPG Character Class
 *
 * Roleplay Character-Class
 * @package Ruins
 */
class RPGCharacter extends Character
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings=false)
    {
        global $dbconnect;

        // Force to use the Characters-Table
        $settings["tablename"] = $dbconnect['prefix']."characters";

        // Call Constructor of the Parent-Class
        parent::__construct($settings);
    }
}
?>
