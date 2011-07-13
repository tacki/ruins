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
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\ClassLoader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Types\Type;
use Common\Controller\Registry;

/**
 * Doctrine Bootstrap
 */

// Application Mode
$applicationMode = "development";

$doctrineConfig = new Configuration;

// Load Annotation Driver (using a dummy Annotation Directory)
$driverImpl = $doctrineConfig->newDefaultAnnotationDriver(array(DIR_TEMP."dummy"));
$doctrineConfig->setMetadataDriverImpl($driverImpl);

// Proxy Settings
if ($applicationMode == "development") {
    $doctrineConfig->setAutoGenerateProxyClasses(true);
} else {
    $doctrineConfig->setAutoGenerateProxyClasses(false);
}
$doctrineConfig->setProxyDir(DIR_COMMON."Proxies");
$doctrineConfig->setProxyNamespace('Proxies');

// Set Table Prefix
$dbconnect = Registry::get('dbconnect');
$evm = new EventManager;
$tablePrefix = new \Common\DoctrineExtensions\TablePrefix($dbconnect['prefix']);
$evm->addEventListener(Events::loadClassMetadata, $tablePrefix);

// Get EntityManager
$dbconnect = Registry::get('dbconnect');
$em = EntityManager::create($dbconnect, $doctrineConfig, $evm);

// Add EntityManager to Registry
Registry::setEntityManager($em);

?>