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
namespace Ruins\Modules\SettingsLink;
use Ruins\Main\Controller\Page;
use Ruins\Modules\ModuleBase;
use Ruins\Common\Interfaces\ModuleInterface;
use Ruins\Common\Controller\Registry;

/**
 * Settings Link Module
 *
 * Add the Settings Link to the shared Container
 * @package Ruins
 */
class SettingsLink extends ModuleBase implements ModuleInterface
{
    /**
     * @see Common\Interfaces.Module::getName()
     */
    public function getName() { return "SettingsLink Module"; }

    /**
     * (non-PHPdoc)
     * @see Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Enables the settings-Link on every Page"; }

    /**
     * @see Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page)
    {
        $user = Registry::getUser();

        if ($user->character && $user->character->loggedin) {
            // add the Supportlink to the toplist
            $page->nav->addLink("Settings", "popup=popup/settings", "shared")
                      ->setDescription("Hier findest du alle Einstellungen zum Spiel");
        }
    }
}
?>