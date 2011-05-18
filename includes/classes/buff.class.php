<?php
/**
 * Buff Class
 *
 * Base Buff-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: buff.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Buff Class
 *
 * Base Buff-Class
 * @package Ruins
 */
class Buff extends DBObject
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings = false)
    {
        // Call Constructor of the Parent-Class
        parent::__construct($settings);
    }

}
?>
