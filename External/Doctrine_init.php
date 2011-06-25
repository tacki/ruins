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
    Doctrine\DBAL\Types\Type;

/**
 * Doctrine Bootstrap
 */

// Application Mode
$applicationMode = "development";

$doctrineConfig = new Doctrine\ORM\Configuration;

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
global $dbconnect;

$evm = new EventManager;
$tablePrefix = new \Common\DoctrineExtensions\TablePrefix($dbconnect['prefix']);
$evm->addEventListener(Events::loadClassMetadata, $tablePrefix);

// Get EntityManager
global $dbconnect;
$em = EntityManager::create($dbconnect, $doctrineConfig, $evm);

// Default Options
//$em->getConnection()->setCharset('utf8');
?>