<?php
/**
 * ObjectLog Class
 *
 * Simple Logging Class to add a Log-Feature to Objects
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: objectlog.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * ObjectLog Class
 *
 * Simple Logging Class to add a Log-Feature to Objects
 * @package Ruins
 */
class ObjectLog extends StackObject
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param int $maxelements Max. Size of the Stack
     */
    function __construct($maxelements=false)
    {
        parent::__construct($maxelements);
    }
}
?>
