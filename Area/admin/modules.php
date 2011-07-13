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
use Main\Manager\Module as ModuleManager;

/**
 * Page Content
 */
$page->set("pagetitle", "Module");
$page->set("headtitle", "Module");

$page->nav->addHead("Navigation")
          ->addLink("Zurück", "page=admin/main");

$moduleListFromDB = ModuleManager::getModuleListFromDatabase();

if (isset($_POST['chooser'])) {
    foreach ($moduleListFromDB as $module) {
        if (!in_array($module->id, $_POST['chooser'])) {
            $module->enabled = false;
        } else {
            $module->enabled = true;
        }
    }
}

$chooserForm = $page->addForm("chooser", false);
$page->output($chooserForm->head("modulelist", $page->url)->getHTML(), true);
$page->nav->addHiddenLink($page->url);

$moduleList = array();
foreach ($moduleListFromDB as $module) {
    $showModule = array();

    $showModule['name']         = $module->name;
    $showModule['description']  = $module->description;

    $showModule['enabled']       = $chooserForm->checkbox("chooser[]", $module->id, false, $module->enabled)->getHTML();

    $moduleList[] = $showModule;
}

// Database Fields to sort by + Headername
$headers = array(	"name"=>"Name",
                    "description"=>"Beschreibung",
                    "enabled"=>"Aktiviert"
);

$page->addTable("modulelist");
$page->getTable("modulelist")->setCSS("messagelist");
$page->getTable("modulelist")->setTabAttributes(false);
$page->getTable("modulelist")->addTabHeader($headers);
$page->getTable("modulelist")->addListArray($moduleList, "firstrow", "firstrow");
$page->getTable("modulelist")->setSecondRowCSS("secondrow");
$page->getTable("modulelist")->load();

$page->getForm("chooser")->setCSS("delbutton");
$page->output($chooserForm->submitButton("Speichern")->getHTML(), true);
$page->output($chooserForm->close()->getHTML(), true);

?>