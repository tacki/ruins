<?php
/**
 * Common Tree Initialization
 */
use Common\Controller\Registry;
use Common\Controller\Cache;
use Common\Controller\Config;


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