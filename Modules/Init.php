<?php
/**
 * Module Tree Initialization
 */

// Add Module Entities
$paths = array();
foreach (\Main\Manager\Module::getModuleListFromFilesystem() as $module) {
    if (file_exists(DIR_MODULES.$module['directory']."Entities")) {
        $paths[] =  DIR_MODULES.$module['directory']."Entities";
    }
}

$em->getConfiguration()->getMetadataDriverImpl()->addPaths($paths);

// Add Module Templates
global $smarty;

if ($smarty instanceof \Smarty) {
    $paths = array();
    foreach (\Main\Manager\Module::getModuleListFromFilesystem() as $module) {
        if (file_exists(DIR_MODULES.$module['directory']."View/Templates")) {
            $paths[] =  DIR_MODULES.$module['directory']."View/Templates";
        }
    }

    $smarty->addTemplateDir($paths);
}
?>