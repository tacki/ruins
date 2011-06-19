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
use Main\Manager;

/**
 * Page Content
 */
$page->set("pagetitle", "Module");
$page->set("headtitle", "Module");

$page->nav->addHead("Navigation")
          ->addLink("Zurück", "page=admin/main");

$moduleListFromDB = Manager\Module::getModuleListFromDatabase();

if (isset($_POST['chooser'])) {
    foreach ($moduleListFromDB as $module) {
        if (!in_array($module->id, $_POST['chooser'])) {
            $module->enabled = false;
        } else {
            $module->enabled = true;
        }
    }
}

$page->addForm("chooser", false);
$page->output($page->getForm("chooser")->head("modulelist", $page->url), true);
$page->nav->addHiddenLink($page->url);

$moduleList = array();
foreach ($moduleListFromDB as $module) {
    $showModule = array();

    $showModule['name']         = $module->name;
    $showModule['description']  = $module->description;

    $showModule['enabled']       = $page->getForm("chooser")->checkbox("chooser[]", $module->id, false, $module->enabled);

    $moduleList[] = $showModule;
}

// Database Fields to sort by + Headername
$headers = array(	"name"=>"Name",
                    "description"=>"Beschreibung",
                    "enabled"=>"Aktiviert"
);

$page->addTable("modulelist", true);
$page->modulelist->setCSS("messagelist");
$page->modulelist->setTabAttributes(false);
$page->modulelist->addTabHeader($headers);
$page->modulelist->addListArray($moduleList, "firstrow", "firstrow");
$page->modulelist->setSecondRowCSS("secondrow");
$page->modulelist->load();

$page->getForm("chooser")->setCSS("delbutton");
$page->output($page->getForm("chooser")->submitButton("Speichern"), true);
$page->output($page->getForm("chooser")->close(), true);

?>