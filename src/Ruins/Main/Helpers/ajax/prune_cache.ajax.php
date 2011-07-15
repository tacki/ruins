<?php
/**
 * Clear SessionStore-Cache
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Ruins\Common\Controller\Registry;

/**
 * Global Includes
 */
require_once("../../../../../app/config/dirconf.cfg.php");
require_once(DIR_BASE."app/main.inc.php");

Registry::get('main.cache')->deleteAll();
?>
