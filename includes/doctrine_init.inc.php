<?php
/**
 * Doctrine Initialization
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: page.header.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

use Doctrine\Common\Cache\ArrayCache,
    Doctrine\Common\Cache\ApcCache,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\DBAL\Types\Type;

$applicationMode = "development";

$config = new Configuration;

// Enable Caching
if ($applicationMode == "development") {
    $cache = new ArrayCache;
} else {
    $cache = new ApcCache;
}
$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);

// Load Annotation Driver
$driverImpl = $config->newDefaultAnnotationDriver(DIR_LIB."Entities");
$config->setMetadataDriverImpl($driverImpl);

// Enable SQL Logger
//$config->setSQLLogger(new Doctrine\DBAL\Logging\EchoSQLLogger);

// Proxy Settings
if ($applicationMode == "development") {
    $config->setAutoGenerateProxyClasses(true);
} else {
    $config->setAutoGenerateProxyClasses(false);
}
$config->setProxyDir(DIR_INCLUDES."proxies");
$config->setProxyNamespace('Proxies');

// Get EntityManager
global $dbconnect;
$em = EntityManager::create($dbconnect, $config);

// Default Options
$em->getConnection()->setCharset('utf8');

?>