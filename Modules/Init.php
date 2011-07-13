<?php
/**
 * Module Tree Initialization
 */

/**
 * Namespaces
 */
use Common\Controller\Registry;
use Main\Manager\Module as ModuleManager;

// Add Module Entities
$paths = array();
foreach (ModuleManager::getModuleListFromFilesystem() as $module) {
    if (file_exists(DIR_MODULES.$module['directory']."Entities")) {
        $paths[] =  DIR_MODULES.$module['directory']."Entities";
    }
}

$em->getConfiguration()->getMetadataDriverImpl()->addPaths($paths);

// Add Module Templates
$smarty = Registry::get('smarty');

if ($smarty instanceof \Smarty) {
    $paths = array();
    foreach (ModuleManager::getModuleListFromFilesystem() as $module) {
        if (file_exists(DIR_MODULES.$module['directory']."View/Templates")) {
            $paths[] =  DIR_MODULES.$module['directory']."View/Templates";
        }
    }

    $smarty->addTemplateDir($paths);
}
?>