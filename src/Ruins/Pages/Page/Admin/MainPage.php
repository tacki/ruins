<?php
/**
 * Admin - Modules
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Admin;
use Ruins\Main\Manager\SystemManager;
use Ruins\Common\Controller\AbstractPageObject;

class MainPage extends AbstractPageObject
{
    public $title  = "Administration";

    public function createContent($page, $parameters)
    {
        foreach (SystemManager::getAdminCategories() as $category) {
            $page->nav->addHead($category);

            foreach (SystemManager::getAdminCategoryPages($category) as $entry) {
                $page->nav->addLink($entry->name, $entry->page);
            }
        }

        $page->addChat("administration_mainchat")->show();
    }
}