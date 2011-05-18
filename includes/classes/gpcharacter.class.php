<?php
/**
 * GP Character Class
 *
 * Gameplay Character-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: gpcharacter.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * GP Character Class
 *
 * Gameplay Character-Class
 * @package Ruins
 */
class GPCharacter extends Character
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
