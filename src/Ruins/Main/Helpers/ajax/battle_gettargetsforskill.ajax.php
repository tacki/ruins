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
use Ruins\Main\Controller\SkillBase;
use Ruins\Main\Manager;
use Ruins\Common\Controller\Registry;

/**
 * Global Includes
 */
require_once("../../../../../app/config/dirconf.cfg.php");
require_once(DIR_BASE."app/main.inc.php");

$systemCache = Registry::getMainConfig();
$em = Registry::getEntityManager();

$battleid	= rawurldecode($_GET['battleid']);
$charid 	= rawurldecode($_GET['charid']);
$skillname	= rawurldecode($_GET['skillname']);

if (isset($battleid) && isset($charid) && isset($skillname)) {

    // Load Battleinformation
    $battle     = $em->find("Main:Battle", $battleid);
    $character  = $em->find("Main:Character", $charid);
    $member     = $em->getRepository("Main:Battle")->getBattleMember($character, $battle);

    // Get fighting Side
    $ourside    = $member->side;
    $otherside	= $member->getOppositeSide();

    // Get the skill the character likes to use
    $skill = $em->getRepository("Main:Skill")->getController($skillname);

    // Retrieve the possible side of the target
    switch ($skill->getPossibleTargets()) {

        case SkillBase::POSSIBLE_TARGET_OWN:
            // Target is self
            $targetside = SkillBase::POSSIBLE_TARGET_OWN;
            break;

        case SkillBase::POSSIBLE_TARGET_ENEMIES:
            // Target is the opposite side
            $targetside = $otherside;
            break;

        case SkillBase::POSSIBLE_TARGET_ALLIES:
            // Target is the own side
            $targetside = $ourside;
            break;
    }

    // Retrieve the number of targets
    $target = false;
    if ($targetside != SkillBase::POSSIBLE_TARGET_OWN) {
        if (is_numeric($skill->getNrOfTargets())) {
            if ($skill->nroftargets == 1) {
                $target = "single";
            } elseif ($skill->nroftargets >= 2) {
                $target = "multiple";
            }
        } elseif ($skill->nroftargets == SkillBase::POSSIBLE_TARGET_ALLIES) {
            $target	= SkillBase::POSSIBLE_TARGET_ALLIES;
        } elseif ($skill->nroftargets == SkillBase::POSSIBLE_TARGET_ENEMIES) {
            $target = SkillBase::POSSIBLE_TARGET_ENEMIES;
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

        case SkillBase::POSSIBLE_TARGET_ALLIES:
            $result[] = SkillBase::POSSIBLE_TARGET_ALLIES;
            break;

        case SkillBase::POSSIBLE_TARGET_ENEMIES:
            $result[] = SkillBase::POSSIBLE_TARGET_ENEMIES;
            break;

    }

    echo json_encode($result);
}

?>
