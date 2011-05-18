<?php
/**
 * AutoComplete AJAX Helper
 *
 * This is the AJAX Interface for Autocomplete
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: autocomplete_charname.ajax.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_INCLUDES."includes.inc.php");

$decodestring = rawurldecode($_GET['part']);

// check the parameter
if(isset($_GET['part']))
{
    // Get userlist from database

    // Use the global Database-Connection
    if (!$result = SessionStore::readCache("ajax_autocomplete_".$_GET['part'])) {
        $dbqt = new QueryTool;

        $result = $dbqt	->select("name")
                        ->from("characters")
                        ->where("name LIKE " . $dbqt->quote($_GET['part'] . "%"))
                        ->where("id > 1") // ignore user SYSTEM
                        ->order("name")
                        ->limit(5, 0)
                        ->exec()
                        ->fetchCol("name");

        SessionStore::writeCache("ajax_autocomplete_".$_GET['part'], $result);
    }

    // return the array as json with PHP 5.2
    echo json_encode($result);
}
