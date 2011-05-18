<?php
/**
 * Example Output Module
 *
 * Module to show, what an output Module can do for you
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: example.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Example Output Module
 *
 * Module to show, what an output Module can do for you
 * @package Ruins
 */
class Example extends Output
{
    /**
     * Module is disabled by default
     */
    public $disabled;

    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "Example Output Module"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Example Output Module"; }

    /**
     * Call Navigation Module
     * @param Nav $nav The Navigation-Object
     */
    public function callNavModule(Nav &$nav)
    {
        // add a header at position 1
        $nav->add(new Link("Example Module"), 1);
        // add a normal link at position 2 (absolute)
        $nav->add(new Link("Example Link1", "page=developer/test"), 2);
        // add a normal link at position 5 (absolute)
        $nav->add(new Link("Example Link2", "page=developer/test"), 5);
        // add a normal link to the end
        $nav->add(new Link("Example Link3", "page=developer/test"));
    }

    /**
     * Call Text Module
     * @param array $body The Content of the Page-Body
     */
    public function callTextModule(&$body)
    {
        // add Text to the Top of the Page
        array_unshift($body, "`b ~Modified by the Example Output Module~ `b`n`n");

        // add Text to the Bottom of the Page
        array_push($body, "`b ~Modified by the Example Output Module~ `b");

        // add line number if line is longer than 20 chars
        $count = 1;
        foreach ($body as &$line) {
            if (strlen($line) > 20) {
                $line = $count++ . ". " . $line;
            }
        }
    }

}
?>
