<?php
/**
 * Admin Link Module
 *
 * Add the Administration Link to the shared Container
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Modules\AdminLink;
use Main\Controller\Page,
    Main\Manager;

/**
 * Admin Link Module
 *
 * Add the Administration Link to the shared Container
 * @package Ruins
 */
class AdminLink extends \Modules\ModuleBase implements \Common\Interfaces\Module
{
    /**
     * (non-PHPdoc)
     * @see Modules.ModuleBase::init()
     */
    public function init()
    {
        // Call Parent init (important!)
        parent::init();

        // Enable by default!
        $entity = $this->getModuleEntity();
        $entity->enabled = true;
    }

    /**
     * @see Common\Interfaces.Module::getModuleName()
     */
    public function getModuleName() { return "AdminLink Module"; }

    /**
     * (non-PHPdoc)
     * @see Common\Interfaces.Module::getModuleDescription()
     */
    public function getModuleDescription() { return "Enables the Admin-Link on every Page"; }

    /**
     * @see Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page)
    {
        global $user;

        if ($user->character && $user->character->loggedin) {
            // Link restricted to Group "Administrator"
            $page->nav->addLink("Administration", "page=admin/main", "shared", Manager\Rights::getGroup("Administrator"))
                      ->setDescription("Administrative Einstellungen");
        }
    }
}
?>