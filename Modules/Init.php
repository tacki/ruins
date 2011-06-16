<?php
/**
 * Module Tree Initialization
 */

// Add Module Entities
foreach (\Main\Manager\Module::getModuleListFromDatabase(true) as $module) {
    $em->getConfiguration()->newDefaultAnnotationDriver($module->basedir."Entities");
}
?>