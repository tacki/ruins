<?php
/**
 * Doctrine Initialization
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Doctrine\Common\Cache\ArrayCache,
    Doctrine\Common\Cache\ApcCache,
    Doctrine\Common\ClassLoader,
    Doctrine\Common\EventManager,
    Doctrine\ORM\Events,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\DBAL\Types\Type;

/**
 * Doctrine Bootstrap
 */

// Application Mode
$applicationMode = "development";

$config = new Configuration;

// Enable Caching
if ($applicationMode == "development") {
    $cache = new ArrayCache;
} else {
    $cache = new ApcCache;
}
$config->setQueryCacheImpl($cache);

// Load Annotation Driver (using a dummy Annotation Directory)
$driverImpl = $config->newDefaultAnnotationDriver(array(DIR_TEMP."dummy"));
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

// Set Table Prefix
global $dbconnect;

$evm = new EventManager;
$tablePrefix = new \Common\DoctrineExtensions\TablePrefix($dbconnect['prefix']);
$evm->addEventListener(Events::loadClassMetadata, $tablePrefix);

// Get EntityManager
global $dbconnect;
$em = EntityManager::create($dbconnect, $config, $evm);

// Default Options
$em->getConnection()->setCharset('utf8');
?>