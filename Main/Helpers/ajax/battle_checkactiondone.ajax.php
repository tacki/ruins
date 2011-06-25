<?php
/**
 * Battlemembers Actionchecker
 *
 * Checks if all Battlemembers on the given Battle have choosen their Action
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_BASE."main.inc.php");

global $em;

$battleid 	= rawurldecode($_GET['battleid']);

if (isset($battleid) && is_numeric($battleid)) {

    $result = array();

    $qb = $em->createQueryBuilder();

    $res = $qb	->select("bm")
                ->from("Main:BattleMember", "bm")
                ->where("bm.side != ?1")->setParameter(1, "neutral")
                ->andWhere("bm.battle = ?2")->setParameter(2, $battleid)
                ->getQuery()->getResult();

    foreach ($res as $entry) {
        $result['battlemembers'][] = $entry->character->id;
    }

    $qb = $em->createQueryBuilder();

    $res = $qb	->select("ba")
                ->from("Main:BattleAction", "ba")
                ->andWhere("ba.battle = ?1")->setParameter(1, $battleid)
                ->getQuery()->getResult();

    foreach ($res as $entry) {
        $result['actiondone'][] = $entry->initiator->id;
    }

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
