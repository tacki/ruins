<?php
/**
 * Common Tree Initialization
 */
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\Cache;
use Ruins\Common\Controller\Config;


// Global Config
Registry::setMainConfig(new Config);

// Global Cache
$systemCache = Cache::retrieve();
Registry::set("main.cache", $systemCache);

// Activate Cache in Doctrine
$em = Registry::getEntityManager();
$em->getConfiguration()->setQueryCacheImpl($systemCache);
$em->getConfiguration()->setMetadataCacheImpl($systemCache);
?>