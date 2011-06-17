<?php
/**
 * Module Tree Initialization
 */

// Add Module Entities
$paths = array();
foreach (\Main\Manager\Module::getModuleListFromDatabase(true) as $module) {
    if (file_exists(DIR_MODULES.$module->basedir."Entities")) {
        $paths[] =  DIR_MODULES.$module->basedir."Entities";
    }
}
$em->getConfiguration()->getMetadataDriverImpl()->addPaths($paths);
?>