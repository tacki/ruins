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
use Common\Controller\Registry;

/**
 * Set timezone
 */
date_default_timezone_set('Europe/Berlin');

/**
 * Doctrine ClassLoaders
 */
require_once(DIR_EXTERNAL."Doctrine/Common/ClassLoader.php");

$classLoader = new ClassLoader('Doctrine', DIR_EXTERNAL);
$classLoader->register();

$classLoader = new ClassLoader('Symfony', DIR_EXTERNAL . 'Doctrine/');
$classLoader->register();

$classLoader = new ClassLoader('Common', DIR_BASE);
$classLoader->register();

$classLoader = new ClassLoader('Main', DIR_BASE);
$classLoader->register();

$classLoader = new ClassLoader('Modules', DIR_BASE);
$classLoader->register();

$classLoader = new ClassLoader('Modules', DIR_BASE."../");
$classLoader->register();

/**
 * Register Database-Connection Information
 */
require_once(DIR_CONFIG."dbconnect.cfg.php");

Registry::set('dbconnect', $dbconnect);


/**
 * Doctrine Initialization
 */
require_once(DIR_EXTERNAL."Doctrine_init.php");

/**
 * Smarty Initialization
 */
require_once(DIR_EXTERNAL."Smarty_init.php");

/**
 * PHP-OpenID Initialization
 */
require_once(DIR_EXTERNAL."php-openid_init.php");

/**
 * Tree Initialization
 */
require_once(DIR_COMMON."Init.php");
require_once(DIR_MAIN."Init.php");
require_once(DIR_MODULES."Init.php");

?>
