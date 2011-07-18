<?php
/**
 * PHP-OpenID Initialization
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */
$oldlevel = error_reporting(0);
$path_extra = DIR_VENDOR . "php-openid";
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);

require_once "Auth/OpenID/Consumer.php";
require_once "Auth/OpenID/FileStore.php";
require_once "Auth/OpenID/SReg.php";
require_once "Auth/OpenID/PAPE.php";

global $pape_policy_uris;
$pape_policy_uris = array(
    PAPE_AUTH_MULTI_FACTOR_PHYSICAL,
    PAPE_AUTH_MULTI_FACTOR,
    PAPE_AUTH_PHISHING_RESISTANT
);
error_reporting($oldlevel);
?>