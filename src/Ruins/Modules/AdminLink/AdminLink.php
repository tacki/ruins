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
namespace Ruins\Modules\AdminLink;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Modules\ModuleBase;
use Ruins\Common\Interfaces\ModuleInterface;
use Ruins\Common\Controller\Registry;
use Ruins\Main\Manager\RightsManager;

/**
 * Admin Link Module
 *
 * Add the Administration Link to the shared Container
 * @package Ruins
 */
class AdminLink extends ModuleBase implements ModuleInterface
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
        $entity = $this->getEntity();
        $entity->enabled = true;
    }

    /**
     * @see Ruins\Common\Interfaces.Module::getName()
     */
    public function getName() { return "AdminLink Module"; }

    /**
     * (non-PHPdoc)
     * @see Ruins\Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Enables the Admin-Link on every Page"; }

    /**
     * @see Ruins\Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(OutputObjectInterface $page)
    {
        $user = Registry::getUser();

        if ($user->character && $user->getCharacter()->loggedin) {
            // Link restricted to Group "Administrator"
            $page->getNavigation("shared")
                 ->addLink("Administration", "Page/Admin/Main", "Administrative Einstellungen", RightsManager::getGroup("Administrator"));
        }
    }
}
?>