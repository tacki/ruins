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
use Main\Manager\SystemManager;

/**
 * Page Content
 */
$page->set("pagetitle", "Administration");
$page->set("headtitle", "Administration");


foreach (SystemManager::getAdminCategories() as $category) {
    $page->nav->addHead($category);

    foreach (SystemManager::getAdminCategoryPages($category) as $entry) {
        $page->nav->addLink($entry->name, $entry->page);
    }
}

SystemManager::getAdminCategories();


$page->addChat("administration_mainchat")->show();

?>