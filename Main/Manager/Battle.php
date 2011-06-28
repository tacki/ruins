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
namespace Main\Manager;
use Common\Controller\Error,
    Main\Entities;

/**
 * Battlesystem Class
 *
 * Class to manage Battles
 * @package Ruins
 */
class Battle
{
    /**
     * Get Skill Object
     * @param string $skillname
     * @throws Common\Controller\Error
     * @return object Skill Object
     */
    public static function getSkill($skillname)
    {
        global $em;

        $result = $em->getRepository("Main:Skill")->findOneByName($skillname);

        if ($result) {
            return new $result->classname;
        } else {
            throw new Error("Skill $skillname not found in Database");
        }
    }

    /**
    * Get the List of Skills from the Filesystem
    * @return array List of Skills
    */
    public static function getSkillListFromFilesystem()
    {
        $result = array();
        $dircontent = \Main\Manager\System::getDirList(DIR_MAIN."Controller/Skills");

        foreach ($dircontent['files'] as $filename) {
            if (strtolower(substr($filename, -4,4) == ".php")) {
                $classname = pathinfo($filename, \PATHINFO_FILENAME);
                $result[] = "Main\\Controller\\Skills\\".$classname;
            }
        }

        return $result;
    }

    /**
    * Get the List of Skills from the Database
    * @return array List of Skills (all Properties)
    */
    public static function getSkillListFromDatabase()
    {
        global $em;

        $result = $em->getRepository("Main:Skill")->findAll();

        return $result;
    }

    /**
    * Synchronize the Skills existing at the Database with the Skills existing in our Directory
    * @return bool true if successful, else false
    */
    public static function syncSkillListToDatabase()
    {
        global $em;

        $skillsFsList = self::getSkillListFromFilesystem();
        $skillsDbList = self::getSkillListFromDatabase();

        foreach($skillsFsList as $skillFS) {
            $addFlag        = true;

            foreach($skillsDbList as $skillDB) {
                if ($skillDB->classname == $skillFS) {
                    $addFlag = false;
                }
            }

            if ($addFlag) {
                // execute init()-Method of unknown Skill
                $skill = new $skillFS;
                $skill->init();
            }
        }

        $em->flush();

        return true;
    }

    /**
     * Get current Battle of a given Character
     * @param Main\Entities\Character $char Character to check
     * @return Main\Entities\Battle
     */
    public static function getBattle(Entities\Character $character)
    {
        global $em;

        $qb = $em->createQueryBuilder();

        $result = $qb   ->select("battlemember")
                        ->from("Main:BattleMember", "battlemember")
                        ->where("battlemember.character = ?2")->setParameter(2, $character)
                        ->getQuery()->getOneOrNullResult();

        if ($result->battle) {
            return $result->battle;
        } else {
            return false;
        }
    }

    /**
     * Get all current Battles
     * @param bool $onlyactive Get only the active ones
     * @return array Array of Battles with all corresponding Data
     */
    public static function getBattleList($onlyactive=false)
    {
        global $em;

        $qb = $em->createQueryBuilder();

        $result = $qb   ->select("battle")
                        ->from("Main:Battle", "battle")
                        ->where("battle.active = ?1")->setParameter(1, $onlyactive, \Doctrine\DBAL\Types\Type::BOOLEAN)
                        ->getQuery()->getResult();

        return $result;
    }

    /**
    * Returns div-box with Information about a given Battle
    * @param Main\Entities\Battle $battle
    */
    public static function showBattleInformationBox(\Main\Entities\Battle $battle)
    {
        $attackerlist = $battle->getAllAttackers();
        $defenderlist = $battle->getAllDefenders();

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
            $target = \Main\Manager\System::getOutputObject()->url->base."&battle_op=join&side=".\Main\Entities\BattleMember::SIDE_ATTACKERS."&battleid=".$battle->id;
            $output .= "<a href='?".$target."'>Angreifen</a>";
            \Main\Manager\System::getOutputObject()->nav->addHiddenLink($target);
            $output .= " || ";
            $target = \Main\Manager\System::getOutputObject()->url->base."&battle_op=join&side=".\Main\Entities\BattleMember::SIDE_DEFENDERS."&battleid=".$battle->id;
            $output .= "<a href='?".$target."'>Verteidigen</a>";
            \Main\Manager\System::getOutputObject()->nav->addHiddenLink($target);
            $output .= " || ";
        }
        $target = \Main\Manager\System::getOutputObject()->url->base."&battle_op=join&side=".\Main\Entities\BattleMember::SIDE_NEUTRALS."&battleid=".$battle->id;
        $output .= "<a href='?".$target."'>Zuschauen</a>";
        \Main\Manager\System::getOutputObject()->nav->addHiddenLink($target);
        $output .= "</div>";

        \Main\Manager\System::getOutputObject()->output($output, true);
    }
}
?>
