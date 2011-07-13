<?php
/**
 * Messenger New Mail Checker
 *
 * Checks if a Character has a new Mail
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
* Namespaces
*/
use Common\Controller\Registry;

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_BASE."main.inc.php");

$em = Registry::getEntityManager();

$userid = rawurldecode($_GET['userid']);

if (isset($userid) && is_numeric($userid)) {
    $qb = $em->createQueryBuilder();

    $result = $qb->select("message.id")
                    ->from("Main:Message", "message")
                    ->where("message.receiver = ?1")->setParameter(1, $userid)
                    ->andWhere("message.status = 0")
                    ->getQuery()->getResult();

    echo json_encode(count($result));
}

?>
