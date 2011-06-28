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
use Main\Controller,
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
    global $em;

    // Load Battleinformation
    $battle     = $em->find("Main:Battle", $battleid);
    $character  = $em->find("Main:Character", $charid);
    $member     = $battle->getMember($character);

    // Get fighting Side
    $ourside    = $member->side;
    $otherside	= $member->getOppositeSide();

    // Get the skill the character likes to use
    $skill = Manager\Battle::getSkill($skillname);

    // Retrieve the possible side of the target
    switch ($skill->getPossibleTargets()) {

        case Controller\SkillBase::POSSIBLE_TARGET_OWN:
            // Target is self
            $targetside = Controller\SkillBase::POSSIBLE_TARGET_OWN;
            break;

        case Controller\SkillBase::POSSIBLE_TARGET_ENEMIES:
            // Target is the opposite side
            $targetside = $otherside;
            break;

        case Controller\SkillBase::POSSIBLE_TARGET_ALLIES:
            // Target is the own side
            $targetside = $ourside;
            break;
    }

    // Retrieve the number of targets
    $target = false;
    if ($targetside != Controller\SkillBase::POSSIBLE_TARGET_OWN) {
        if (is_numeric($skill->getNrOfTargets())) {
            if ($skill->nroftargets == 1) {
                $target = "single";
            } elseif ($skill->nroftargets >= 2) {
                $target = "multiple";
            }
        } elseif ($skill->nroftargets == Controller\SkillBase::POSSIBLE_TARGET_ALLIES) {
            $target	= Controller\SkillBase::POSSIBLE_TARGET_ALLIES;
        } elseif ($skill->nroftargets == Main\Controller\SkillBase::POSSIBLE_TARGET_ENEMIES) {
            $target = Controller\SkillBase::POSSIBLE_TARGET_ENEMIES;
        }
    }

    // Build the result
    $result = array();

    switch ($target) {

        default:
            // We target ourself
            $result[$member->id] = $member->character->name;
            break;

        case "single":
        case "multiple":
            $memberids = array();
            if ($skill->possibletargets == SKILL_POSSIBLE_TARGET_ENEMIES) {
                // We target one enemy or more
                $members = $battle->getMemberList($otherside);
            } elseif ($skill->possibletargets == SKILL_POSSIBLE_TARGET_ALLIES) {
                // We target one ally or more
                $members = $battle->getMemberList($ourside);
            }

            foreach ($members as $member) {
                $result[$member->id] = $member->character->name;
            }
            break;

        case Controller\SkillBase::POSSIBLE_TARGET_ALLIES:
            $result[] = Controller\SkillBase::POSSIBLE_TARGET_ALLIES;
            break;

        case Controller\SkillBase::POSSIBLE_TARGET_ENEMIES:
            $result[] = Controller\SkillBase::POSSIBLE_TARGET_ENEMIES;
            break;

    }

    echo json_encode($result);
}

?>
