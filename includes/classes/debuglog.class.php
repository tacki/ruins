<?php
/**
 * DebugLog Class
 *
 * Class to handle Debuglogentries
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: debuglog.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * DebugLog Class
 *
 * Class to handle Debuglogentries
 * @package Ruins
 */
class DebugLog extends EntityHandler
{
    /**
     * constructor - load the default values and initialize the attributes
     */
    function __construct()
    {
        $this->entity = new Entities\DebuglogEntity;
    }

    /**
     * Add a new DebugLog Entry
     * @param string $text The Text to add
     * @param string $level The Debuglevel ("none", "default", "verbose" or "veryverbose")
     * @return mixed The ID of the new DebugEntry in the Database or false if something went wrong
     */
    public function add($text, $level="default")
    {
        global $em;

        $this->entry = $text;

        $em->persist($this->entity);
    }
}