<?php
/**
 * SettingsLink Module
 *
 * Add a Settingslink to every Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Nav,
    Main\Controller\Link;

/**
 * SettingsLink Module
 *
 * Add a Settingslink to every Page
 * @package Ruins
 */
class SettingsLink extends Output
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "Settingslink Module"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Module to add a Settings Link to each Page"; }

    /**
     * Call Navigation Module
     * @param Nav $nav The Navigation-Object
     */
    public function callNavModule(Nav &$nav)
    {
        global $user;

        if (isset($user) && $user->loggedin) {
            // add the Supportlink to the toplist
            $nav->add(new Link("Einstellungen", "popup=popup/settings", "shared", "Hier findest du alle Einstellungen zum Spiel"));
        }
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
