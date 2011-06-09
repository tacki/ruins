<?php
/**
 * Smarty Initialization
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

require_once(DIR_COMMON_EXTERNAL."smarty/Smarty.class.php");

// Initialize the Smarty-Class
$smarty = new \Smarty();

// Enable Caching with endless lifetime
$smarty->caching        = 1;
$smarty->cache_lifetime = -1;

$smarty->template_dir 	= DIR_TEMPLATES;
$smarty->compile_dir 	= DIR_TEMP."smarty/templates_c";
$smarty->cache_dir 		= DIR_TEMP."smarty/cache";
$smarty->config_dir     = DIR_INCLUDES_SMARTY."configs";
?>