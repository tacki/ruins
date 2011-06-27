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
namespace Modules\ToolBox;
use Main\Controller\Page,
    Main\Controller\Link;

/**
 * ToolBox Module
 *
 * Adds the ToolBox
 * @package Ruins
 */
class ToolBox extends \Modules\ModuleBase implements \Common\Interfaces\Module
{
    /**
     * @see Common\Interfaces.Module::getName()
     */
    public function getName() { return "ToolBox Module"; }

    /**
     * @see Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Add the Toolbox for all kind of usefull small tools"; }

    /**
     * @see Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page)
    {
        global $user;

        // Tools

        // Prune Cache Tool
        if ($user->character && $user->character->loggedin) {

            $page->addToolBoxItem(new Link("prunecache", "prune_cache.ajax.php"),
                                  "Session Cache leeren",
                                  \Main\Manager\System::getOverloadedFilePath("View/Images/trash.png", true),
                                  \Main\Manager\System::getOverloadedFilePath("View/Images/accept.png", true)
                                );
        }
    }
}
?>