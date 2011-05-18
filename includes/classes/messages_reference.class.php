<?php
/**
 * Messages_Reference Class
 *
 * Single Message_Ref Object
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: messages_reference.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Messages_Reference Class
 *
 * Single Message_Ref Object
 * @package Ruins
 */
class Messages_Reference extends DBObject
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings=false)
    {
        // Call Constructor of the Parent-Class
        parent::__construct($settings);
    }
}
?>
