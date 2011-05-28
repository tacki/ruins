<?php
/**
 * Global Includes File
 *
 * Global Includes File - should be included on every page!
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006-2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Database-Connection Information
 */
require_once(DIR_CONFIG."dbconnect.cfg.php");

/**
 * Doctrine ClassLoaders
 */
require_once(DIR_INCLUDES_DOCTRINE."vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php");

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', DIR_INCLUDES_DOCTRINE . 'vendor/doctrine-common/lib');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\DBAL', DIR_INCLUDES_DOCTRINE . 'vendor/doctrine-dbal/lib');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\ORM', DIR_INCLUDES_DOCTRINE);
$classLoader->register();

$classloader = new \Doctrine\Common\ClassLoader('Symfony', DIR_INCLUDES_DOCTRINE . 'vendor/');
$classloader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Entities', DIR_INCLUDES . 'entities');
$classLoader->register();

/**
 * Doctrine Initialization
 */
require_once(DIR_INCLUDES."doctrine_init.inc.php");

/**
 * Global Functions
 */
require_once(DIR_INCLUDES."functions/global.func.php");
/**
 * File Functions
 */
require_once(DIR_INCLUDES."functions/file.func.php");
/**
 * Database Functions
 */
require_once(DIR_INCLUDES."functions/database.func.php");

?>
