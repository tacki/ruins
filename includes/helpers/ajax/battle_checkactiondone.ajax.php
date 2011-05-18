<?php
/**
 * Battlemembers Actionchecker
 *
 * Checks if all Battlemembers on the given Battle have choosen their Action
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: battle_checkactiondone.ajax.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_INCLUDES."includes.inc.php");

$battleid 	= rawurldecode($_GET['battleid']);

if (isset($battleid) && is_numeric($battleid)) {

    $result = array();

    $dbqt = new QueryTool;

    // Get List of all Battlemembers
    $result['battlemembers'] = $dbqt	->select("characterid")
                                    ->from("battlemembers")
                                    ->where("side != ". $dbqt->quote("neutral"))
                                    ->where("battleid = " . $dbqt->quote($battleid))
                                    ->exec()
                                    ->fetchCol("characterid");

    $dbqt->clear();

    // Get List of Battlemembers which made a move
    $result['actiondone'] = $dbqt	->select("initiatorid")
                                ->from("battletable")
                                ->where("battleid = " . $dbqt->quote($battleid))
                                ->exec()
                                ->fetchCol("initiatorid");

    // Calc List of Battlemembers which didn't made a move
    $result['waitingfor'] = array_diff($result['battlemembers'], $result['actiondone']);

    // Result (example)
    // array(
    //		waitingfor: array( 3,5,8 )
    //		actiondone: array( 6 )
    //		battlemembers: array( 3,5,6,8 )
    // )

    echo json_encode($result);
}

?>
