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

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\DBAL\Types\Type;

$applicationMode = "development";

if ($applicationMode == "development") {
    $cache = new \Doctrine\Common\Cache\ArrayCache;
} else {
    $cache = new \Doctrine\Common\Cache\ApcCache;
}

$logger = new \Doctrine\DBAL\Logging\EchoSQLLogger;

$config = new Configuration;
$config->setMetadataCacheImpl($cache);
$driverImpl = $config->newDefaultAnnotationDriver(DIR_INCLUDES."entities");
$driverImpl->setFileExtension(".entity.php");
$config->setMetadataDriverImpl($driverImpl);
$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);
//$config->setSQLLogger($logger);
$config->setProxyDir(DIR_INCLUDES."proxies");
$config->setProxyNamespace('Proxies');

if ($applicationMode == "development") {
    $config->setAutoGenerateProxyClasses(true);
} else {
    $config->setAutoGenerateProxyClasses(false);
}

global $dbconnect;
$connectionOptions = $dbconnect;

$em = EntityManager::create($connectionOptions, $config);

?>