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
namespace Ruins\Modules\Support;
use Ruins\Main\Controller\Page;
use Ruins\Modules\ModuleBase;
use Ruins\Common\Interfaces\ModuleInterface;
use Ruins\Common\Controller\Registry;

/**
 * Support Module
 *
 * Add a Supportlink to every Page
 * @package Ruins
 */
class Support extends ModuleBase implements ModuleInterface
{
    /**
     * @see Ruins\Common\Interfaces.Module::getName()
     */
    public function getName() { return "Supportlink Module"; }

    /**
     * @see Ruins\Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Module to add a Support-Link to each Page"; }

    /**
     * @see Ruins\Modules.ModuleBase::prePageHeader()
     */
    public function prePageHeader()
    {
        // Page preparation
        $config = Registry::getMainConfig();

        // Page preparation
        $config->addPublicPage(array("/popup/support"));
    }

    /**
     * @see Ruins\Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page)
    {
        $page->nav->addLink("Support", "Popup/SupportPopup", "shared")
                  ->setDescription("Wenn ein Fehler oder Bug auftritt, bitte hier melden");
    }
}
?>