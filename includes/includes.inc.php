<?php
/**
 * Global Includes File
 *
 * Global Includes File - should be included on every page!
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: includes.inc.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Database-Connection Information
 */
require_once(DIR_CONFIG."dbconnect.cfg.php");
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
