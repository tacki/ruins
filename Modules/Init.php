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
?>