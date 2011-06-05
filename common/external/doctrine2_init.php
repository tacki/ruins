<?php
/**
 * Doctrine Initialization
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
$config->setQueryCacheImpl($cache);

// Load Annotation Driver
$driverImpl = $config->newDefaultAnnotationDriver(DIR_MAIN."Entities");
$config->setMetadataDriverImpl($driverImpl);
$config->setMetadataCacheImpl($cache);

// Enable SQL Logger
//$config->setSQLLogger(new Doctrine\DBAL\Logging\EchoSQLLogger);

// Proxy Settings
if ($applicationMode == "development") {
    $config->setAutoGenerateProxyClasses(true);
} else {
    $config->setAutoGenerateProxyClasses(false);
}
$config->setProxyDir(DIR_COMMON."Proxies");
$config->setProxyNamespace('Proxies');

// Get EntityManager
global $dbconnect;
$em = EntityManager::create($dbconnect, $config);

// Default Options
$em->getConnection()->setCharset('utf8');

// Validate Entities
if ($applicationMode == "development") {
    $validator = new \Doctrine\ORM\Tools\SchemaValidator($em);
    $error = $validator->validateMapping();
    if($error) var_dump($error);
}

?>