<?php
/**
 * Battlesystem Class
 *
 * Class to manage Battles
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Main\Manager;
use Ruins\Common\Manager\RequestManager;

use Ruins\Main\Manager\SystemManager;
use Ruins\Main\Entities\Battle;
use Ruins\Main\Entities\BattleMember;
use Ruins\Main\Entities\Character;
use Ruins\Common\Controller\Registry;

/**
 * Battlesystem Class
 *
 * Class to manage Battles
 * @package Ruins
 */
class BattleManager
{
    /**
     * Returns div-box with Information about a given Battle
     * @param Ruins\Main\Entities\Battle $battle
     */
    public static function showBattleInformationBox(Battle $battle)
    {
        $em = Registry::getEntityManager();

        $battleRepository = $em->getRepository("Main:Battle");

        $attackerlist = $battleRepository->getAllAttackers($battle);
        $defenderlist = $battleRepository->getAllDefenders($battle);

        $output = "<div class='floatleft battleinfo'>";
        if (count($attackerlist)) {
            $output .= "Angreifer: ";
            foreach ($attackerlist as $member) {
                $output .= $member->character->displayname . " ";
            }
            $output .= "`n";
        }
        if (count($defenderlist)) {
            $output .= "Verteidiger: ";
            foreach ($defenderlist as $member) {
                $output .= $member->character->displayname . " ";
            }
            $output .= "`n";
        }

        if (!$battle->isActive()) {
            $target = SystemManager::getOutputObject()->getUrl()->getBase()."&battle_op=join&side=".BattleMember::SIDE_ATTACKERS."&battleid=".$battle->id;
            $output .= "<a href='?".$target."'>Angreifen</a>";
            SystemManager::getOutputObject()->nav->addHiddenLink($target);
            $output .= " || ";
            $target = SystemManager::getOutputObject()->getUrl()->getBase()."&battle_op=join&side=".BattleMember::SIDE_DEFENDERS."&battleid=".$battle->id;
            $output .= "<a href='?".$target."'>Verteidigen</a>";
            SystemManager::getOutputObject()->nav->addHiddenLink($target);
            $output .= " || ";
        }
        $target = SystemManager::getOutputObject()->getUrl()->getBase()."&battle_op=join&side=".BattleMember::SIDE_NEUTRALS."&battleid=".$battle->id;
        $output .= "<a href='?".$target."'>Zuschauen</a>";
        SystemManager::getOutputObject()->nav->addHiddenLink($target);
        $output .= "</div>";

        SystemManager::getOutputObject()->output($output, true);
    }

    /**
    * Returns Battle Member List
    * @param Ruins\Main\Entities\Battle $battle
    */
    public static function showBattleMemberlist(Battle $battle)
    {
        $em = Registry::getEntityManager();

        $battleRepository = $em->getRepository("Main:Battle");

        $output = "";

        foreach (array(BattleMember::SIDE_ATTACKERS=>"Angreifer", BattleMember::SIDE_DEFENDERS=>"Verteidiger") as $sysname=>$realname) {
            $output .= "`n$realname: `n";

            $temparray = array();

            foreach ($battleRepository->getAllMembersAtSide($sysname, false, $battle) as $member) {

                if ($member->hasMadeAnAction()) {
                    $transparentstyle = "style=\"opacity: 0.5; filter: alpha(opacity=50); filter: 'progid:DXImageTransform.Microsoft.Alpha(Opacity=50, FinishOpacity=50, Style=2)'\"";
                } else {
                    $transparentstyle = "";
                }
                $temparray[] = "<span id='action_".$member->character->id."' $transparentstyle>".$member->character->displayname." HP: ".$member->character->healthpoints."/".$member->character->lifepoints."</span>";
            }

            $output .= implode(", ", $temparray);
        }

        $neutrallist = $battleRepository->getAllMembersAtSide(BattleMember::SIDE_NEUTRALS, false, $battle);

        if (count($neutrallist)) {
            $output .= "`nZuschauer: `n";
            foreach ($neutrallist as $entry) {
                $output .= $entry->character->displayname . " ";
            }
            $output .= "`n";
        }

        SystemManager::getOutputObject()->output($output, true);
    }

    /**
    * Returns skillchooser Form
    * @param Ruins\Main\Entities\Character $character
    * @param Ruins\Main\Entities\Battle $battle
    */
    public static function showSkillChooser(Character $character, Battle $battle)
    {
        $em = Registry::getEntityManager();

        $battleRepository = $em->getRepository("Main:Battle");

        $output = "";

        $member = $battleRepository->getBattleMember($character, $battle);

        if ($member->isNeutral()) {
            // Caller is Neutral
            $output .= "Beobachte den Kampf...";
        } elseif ($member->hasMadeAnAction()) {
            // Caller made his Action
            $output .= "Warte auf andere Kämpfer...";
        } else {
            // Show the Skillchooser
            $skillForm = SystemManager::getOutputObject()->addForm("skillchooser");

            $skillForm->head("skillchooser", SystemManager::getOutputObject()->getUrl()->getBase()."&battle_op=use_skill");

            // Add Nav
            SystemManager::getOutputObject()->nav->addHiddenLink(SystemManager::getOutputObject()->getUrl()->getBase()."&battle_op=use_skill");

            // TODO: Get Available Skills for this Character
            $skills = array ( "Heilen" );

            $skillForm->setCSS("input");
            $skillForm->selectStart("skill");
            foreach ($skills as $skill) {
                // Retrieve Classname from Entity
                $classname = $em->getRepository("Main:Skill")->findOneByName($skill)->classname;
                // Create Instance of Skill-Class
                $tempskill = new $classname;
                $skillForm->selectOption($tempskill->getName(), $tempskill->getName(), false, $tempskill->getDescription());
            }
            $skillForm->selectEnd();

            $skillForm->selectStart("target");
            $skillForm->selectEnd();

            $skillForm->submitButton("Ausführen");
            $skillForm->close();
            $output .= "<span id='skilldescription' class='floatclear'></span>";

            // Target-Chooser
            // The third Parameter is the name of the select-Form where we choose the skill
            // The fourth Parameter is the name of the select-Form where the targets appear
            SystemManager::getOutputObject()->addJavaScript("$(function(){
                                            getTargetList(".$battle->id.", ".$character->id.", 'skill', 'target', '".RequestManager::getWebBasePath("Json/Battle/GetTargetsForSkill")."');
                });");
        }

        SystemManager::getOutputObject()->output($output, true);
    }


}
?>
