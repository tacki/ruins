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
 * Namespaces
 */
use Doctrine\Common\ClassLoader;
use Ruins\Common\Controller\Registry;

/**
 * Set timezone
 */
date_default_timezone_set('Europe/Berlin');

/**
 * Doctrine ClassLoaders
 */
require_once(DIR_VENDOR."Doctrine/Common/ClassLoader.php");

$classLoader = new ClassLoader('Doctrine', DIR_VENDOR);
$classLoader->register();

$classLoader = new ClassLoader('Symfony', DIR_VENDOR . 'Doctrine/');
$classLoader->register();

$classLoader = new ClassLoader('DoctrineExtensions', DIR_VENDOR);
$classLoader->register();

$classLoader = new ClassLoader('Ruins', DIR_BASE."src/");
$classLoader->register();

/**
 * Register Database-Connection Information
 */
require_once(DIR_CONFIG."dbconnect.cfg.php");

Registry::set('dbconnect', $dbconnect);


/**
 * Doctrine Initialization
 */
require_once(DIR_VENDOR."Doctrine_init.php");

/**
 * Smarty Initialization
 */
require_once(DIR_VENDOR."Smarty_init.php");

/**
 * PHP-OpenID Initialization
 */
require_once(DIR_VENDOR."php-openid_init.php");

/**
 * Tree Initialization
 */
require_once(DIR_COMMON."Init.php");
require_once(DIR_MAIN."Init.php");
require_once(DIR_MODULES."Init.php");

?>
