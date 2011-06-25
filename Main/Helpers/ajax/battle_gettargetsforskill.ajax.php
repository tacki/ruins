<?php
/**
 * Battle: Get Targetlist
 *
 * Returns a List of targets for a given Skill and Character
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Common\Controller\SessionStore,
    Main\Manager;

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_BASE."main.inc.php");

global $systemCache;

$battleid	= rawurldecode($_GET['battleid']);
$charid 	= rawurldecode($_GET['charid']);
$skillname	= rawurldecode($_GET['skillname']);

if (isset($battleid) && isset($charid) && isset($skillname)) {

    // Load Battleinformation
    $battle		= new Battle;
    $battle->load($battleid);

    $charinfo 	= $battle->getMemberEntry($charid);
    $side		= $charinfo['side'];
    $otherside	= $battle->getOppositeSide($charid);

    // Get the skill the character likes to use
    $skill = ModuleSystem::getSkillModule($skillname);

    // Retrieve the possible side of the target
    $targetside = "own";

    switch ($skill->possibletargets) {

        case SKILL_POSSIBLE_TARGET_ENEMIES:
            $targetside = $otherside;
            break;

        case SKILL_POSSIBLE_TARGET_ALLIES:
            $targetside = $side;
            break;
    }

    // Retrieve the number of targets
    $target = false;
    if ($targetside != "own") {
        if (is_numeric($skill->nroftargets)) {
            if ($skill->nroftargets == 1) {
                $target = "single";
            } elseif ($skill->nroftargets >= 2) {
                $target = "multiple";
            }
        } elseif ($skill->nroftargets == 'allies') {
            $target	= "allies";
        } elseif ($skill->nroftargets == "enemies") {
            $target = "enemies";
        }
    }

    // Build the result
    $result = array();

    switch ($target) {

        default:
            // We target ourself
            $result[$charid] = Manager\User::getCharacterName($charid, false);
            break;

        case "single":
        case "multiple":
            $memberids = array();
            if ($skill->possibletargets == SKILL_POSSIBLE_TARGET_ENEMIES) {
                // We target one enemy or more
                $memberids = $battle->getMemberList($otherside);
            } elseif ($skill->possibletargets == SKILL_POSSIBLE_TARGET_ALLIES) {
                // We target one ally or more
                $memberids = $battle->getMemberList($side);
            }
            foreach ($memberids as $id) {
                $result[$id] = Manager\User::getCharacterName($id, false);
            }
            break;

        case "allies":
            $result[] = "allies";
            break;

        case "enemies":
            $result[] = "enemies";

    }

    echo json_encode($result);
}

?>
