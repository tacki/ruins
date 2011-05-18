<?php
/**
 * Messenger New Mail Checker
 *
 * Checks if a Character has a new Mail
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: messenger_newMessageAlert.ajax.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_INCLUDES."includes.inc.php");

$userid = rawurldecode($_GET['userid']);

if (isset($userid) && is_numeric($userid)) {
    $dbqt = new QueryTool;
    $result = $dbqt	->select("id")
                    ->table("messages_references")
                    ->where("receiver=".$userid)
                    ->where("status=0")
                    ->exec()
                    ->numRows();
    echo json_encode($result);
}

?>
