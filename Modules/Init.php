<?php
/**
 * Module Tree Initialization
 */

// Add Module Entities
foreach (\Main\Manager\Module::getModuleListFromDatabase(true) as $module) {
    $em->getConfiguration()->newDefaultAnnotationDriver($module->namespace."/Entities");
    $entityPaths[] = DIR_BASE."Modules/Support/Entities";
}
?>