<?php
/**
 * Support Module
 *
 * Add a Supportlink to every Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: supportlink.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Support Module
 *
 * Add a Supportlink to every Page
 * @package Ruins
 */
class SupportLink extends Output
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "Supportlink Module"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Module to add a Support-Link to each Page"; }

    /**
     * Call Navigation Module
     * @param Nav $nav The Navigation-Object
     */
    public function callNavModule(Nav &$nav)
    {
        // add the Supportlink to the toplist
        $nav->add(new Link("Support", "popup=popup/support", "shared", "Wenn ein Fehler oder Bug auftritt, bitte hier melden"));
    }

    /**
     * Call Text Module
     * @param array $body The Content of the Page-Body
     */
    public function callTextModule(&$body)
    {
    }

}
?>
