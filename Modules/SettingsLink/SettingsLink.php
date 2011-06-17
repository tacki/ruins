<?php
/**
 * Settings Link Module
 *
 * Add the Settings Link to the shared Container
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Modules\SettingsLink;
use Main\Controller\Page,
    Main\Controller\Link;

/**
 * Settings Link Module
 *
 * Add the Settings Link to the shared Container
 * @package Ruins
 */
class SettingsLink extends \Modules\ModuleBase implements \Common\Interfaces\Module
{
    /**
     * @see Common\Interfaces.Module::getModuleName()
     */
    public function getModuleName() { return "SettingsLink Module"; }

    /**
     * (non-PHPdoc)
     * @see Common\Interfaces.Module::getModuleDescription()
     */
    public function getModuleDescription() { return "Enables the settings-Link on every Page"; }

    /**
     * @see Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page)
    {
        global $user;

        if ($user->character && $user->character->loggedin) {
            // add the Supportlink to the toplist
            $page->nav->addLink("Settings", "popup=popup/settings", "shared")
                      ->setDescription("Hier findest du alle Einstellungen zum Spiel");
        }
    }
}
?>