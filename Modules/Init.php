<?php
/**
 * Module Tree Initialization
 */

// Add Module Entities
foreach (\Main\Manager\Module::getModuleListFromDatabase(true) as $module) {
    $path = str_replace("\\", "/", $module->namespace);
    $em->getConfiguration()->newDefaultAnnotationDriver($path."/Entities");
}
?>