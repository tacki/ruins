<?php
/**
 * Main Tree Initialization
 */

// Add Entity Directory
$em->getConfiguration()->getMetadataDriverImpl()->addPaths(array(DIR_MAIN."Entities"));

//Add Entity Alias
$em->getConfiguration()->addEntityNamespace("Main", "Main\Entities");
?>