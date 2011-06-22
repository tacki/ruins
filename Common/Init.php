<?php
/**
 * Common Tree Initialization
 */

// Global Config
$systemConfig = new Common\Controller\Config;

// Global Cache
$systemCache = Common\Controller\Cache::retrieve();

// Activate Cache in Doctrine
$em->getConfiguration()->setQueryCacheImpl($systemCache);
$em->getConfiguration()->setMetadataCacheImpl($systemCache);
?>