<?php
/**
 * Support Module
 *
 * Add a Supportlink to every Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Modules\Support;
use Main\Controller\Nav,
    Main\Controller\Link;

/**
 * Support Module
 *
 * Add a Supportlink to every Page
 * @package Ruins
 */
class Support
{

    /**
     * Module Initialization
     */
    public function init()
    {
        global $em;

        $module                 = new \Main\Entities\Module;
        $module->name           = self::getModuleName();
        $module->description    = self::getModuleDescription();
        $module->filesystemname = __CLASS__;

        $em->persist($module);
    }

    /**
     * Module Name
     */
    public function getModuleName() { return "Supportlink Module"; }

    /**
     * Module Description
     */
    public function getModuleDescription() { return "Module to add a Support-Link to each Page"; }

    /**
     * Call Navigation Module
     * @param Nav $nav The Navigation-Object
     */
    public function callNavModule(Nav &$nav)
    {
        global $page;

        if ($page instanceof \Main\Controller\Page)
            $page->nav->add(new Link("Support", "popup=popup/support", "shared", "Wenn ein Fehler oder Bug auftritt, bitte hier melden"));
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