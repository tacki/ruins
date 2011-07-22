<?php
/**
 * Smarty Initialization
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Ruins\Common\Controller\Registry;
use Ruins\Main\Controller\Page;

require_once(DIR_VENDOR."Smarty/libs/Smarty.class.php");

// Initialize the Smarty-Class
$smarty = new \Smarty();

// Enable Caching with endless lifetime
$smarty->caching        = 1;
$smarty->cache_lifetime = -1;

//$smarty->compile_check = true;
//$smarty->debugging = true;

$smarty->setTemplateDir(
                        array( "default" => DIR_WEB."main/templates/default")
                       );
$smarty->compile_dir 	= DIR_TEMP."smarty/templates_c";
$smarty->cache_dir 		= DIR_TEMP."smarty/cache";

// Add to Registry
Registry::set('smarty', $smarty);
?>