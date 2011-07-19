<?php
/**
 * ToolBox Module
 *
 * Adds the ToolBox
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Modules\ToolBox;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Main\Controller\Link;
use Ruins\Main\Manager\SystemManager;
use Ruins\Modules\ModuleBase;
use Ruins\Common\Interfaces\ModuleInterface;
use Ruins\Common\Controller\Registry;

/**
 * ToolBox Module
 *
 * Adds the ToolBox
 * @package Ruins
 */
class ToolBox extends ModuleBase implements ModuleInterface
{
    /**
     * @see Ruins\Common\Interfaces.Module::getName()
     */
    public function getName() { return "ToolBox Module"; }

    /**
     * @see Ruins\Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Add the Toolbox for all kind of usefull small tools"; }

    /**
     * @see Ruins\Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(OutputObjectInterface $page)
    {
        $user = Registry::getUser();

        // Tools

        // Prune Cache Tool
        if ($user->character && $user->character->loggedin) {

            $page->addToolBoxItem("pruneCache",
                                  SystemManager::getOverloadedFilePath("Helpers/ajax/prune_cache.ajax.php", true),
                                  "Session Cache leeren",
                                  SystemManager::getWebRessourcePath("images/trash.png", true),
                                  SystemManager::getWebRessourcePath("images/accept.png", true)
                                );
        }
    }
}
?>