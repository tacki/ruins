<?php
/**
 * ToolBox Module
 *
 * Enables the ToolBox
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Controller\Nav,
    Controller\Link;

/**
 * ToolBox Module
 *
 * Enables the ToolBox
 * @package Ruins
 */
class ToolBox extends Output
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "ToolBox Module"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Module to enable the ToolBox (small Links with useful Tools)"; }

    /**
     * Module Initialization
     */
    public function init()
    {
        // Initialize Parent
        parent::init();

        global $user;

        if (isset($user) && $user->loggedin) {
            // Tools

            // Prune Cache
            $this->outputObject->addToolBoxItem(new Link("prunecache", "prune_cache.ajax.php"),
                                                    "Session Cache leeren",
                                                    \Manager\System::htmlpath(DIR_TEMPLATES."/common/images/trash.png"),
                                                    \Manager\System::htmlpath(DIR_TEMPLATES."/common/images/accept.png"));
        }

    }

    /**
     * Call Navigation Module
     * @param Nav $nav The Navigation-Object
     */
    public function callNavModule(Nav &$nav)
    {
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
