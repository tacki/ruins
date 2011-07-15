<?php
/**
 * Main Tree Initialization
 */
use Ruins\Common\Controller\Registry;

$em = Registry::getEntityManager();

// Add Entity Directory
$em->getConfiguration()->getMetadataDriverImpl()->addPaths(array(DIR_MAIN."Entities"));

//Add Entity Alias
$em->getConfiguration()->addEntityNamespace("Main", "Ruins\Main\Entities");
?>