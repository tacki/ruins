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
use Common\Controller\Registry;

require_once(DIR_EXTERNAL."Smarty/Smarty.class.php");

// Initialize the Smarty-Class
$smarty = new \Smarty();

// Enable Caching with endless lifetime
$smarty->caching        = 1;
$smarty->cache_lifetime = -1;

$smarty->setTemplateDir(DIR_BASE. Main\Controller\Page::DEFAULT_PUBLIC_TEMPLATE);
$smarty->compile_dir 	= DIR_TEMP."smarty/templates_c";
$smarty->cache_dir 		= DIR_TEMP."smarty/cache";
$smarty->config_dir     = DIR_EXTERNAL."Smarty/configs";

// Add to Registry
Registry::set('smarty', $smarty);
?>