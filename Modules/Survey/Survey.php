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
namespace Modules\Survey;
use Main\Controller\Page;
use Common\Controller\Registry;

/**
 * Survey Module
 *
 * Create and Handle Surveys
 * @package Ruins
 */
class Survey extends \Modules\ModuleBase implements \Common\Interfaces\Module
{
    /**
     * (non-PHPdoc)
     * @see Modules.ModuleBase::init()
     */
    public function init()
    {
        // Call init of Parent
        parent::init();

        \Main\Manager\System::addAdminPage("Umfragen", "Module", "page=Admin/Survey");
    }

    /**
     * @see Common\Interfaces.Module::getName()
     */
    public function getName() { return "Survey Module"; }

    /**
     * @see Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Create and Handle Surveys"; }

    /**
    * @see Common\Interfaces.Module::prePageGeneration()
    */
    public function prePageGeneration(Page $page)
    {
        $user = Registry::getUser();

        if ($user->character && $user->character->loggedin) {
            $page->nav->addLink("Umfragen", "popup=Popup/Survey", "shared")
                      ->setDescription("Aktuelle Umfragen");
        }
    }
}
?>