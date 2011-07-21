<?php
/**
 * Survey Module
 *
 * Create and Handle Surveys
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Modules\Survey;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Main\Manager\SystemManager;
use Ruins\Modules\ModuleBase;
use Ruins\Common\Interfaces\ModuleInterface;
use Ruins\Common\Controller\Registry;

/**
 * Survey Module
 *
 * Create and Handle Surveys
 * @package Ruins
 */
class Survey extends ModuleBase implements ModuleInterface
{
    /**
     * (non-PHPdoc)
     * @see Modules.ModuleBase::init()
     */
    public function init()
    {
        // Call init of Parent
        parent::init();

        SystemManager::addAdminPage("Umfragen", "Module", "page/Admin/Survey");
    }

    /**
     * @see Ruins\Common\Interfaces.Module::getName()
     */
    public function getName() { return "Survey Module"; }

    /**
     * @see Ruins\Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Create and Handle Surveys"; }

    /**
    * @see Ruins\Common\Interfaces.Module::prePageGeneration()
    */
    public function prePageGeneration(OutputObjectInterface $page)
    {
        $user = Registry::getUser();

        if ($user->character && $user->getCharacter()->loggedin) {
            $page->getNavigation("shared")
                 ->addLink("Umfragen", "Popup/Survey", "Aktuelle Umfragen");
        }
    }
}
?>