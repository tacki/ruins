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
use Main\Entities;

/**
 * Battlesystem Class
 *
 * Class to manage Battles
 * @package Ruins
 */
class Battle
{
    /**
     * Get current Battle ID of a given Character
     * @param Character $char Character to check
     * @return bool true if the char is a Member, else false
     */
    public static function getBattleID(Entities\Character $character)
    {
        $qb = getQueryBuilder();

        $result = $qb   ->select("battlemember")
                        ->from("Main:BattleMember", "battlemember")
                        ->where("battlemember.character = ?2")->setParameter(2, $character)
                        ->getQuery()->getOneOrNullResult();

        if ($result) {
            return $result->battle->id;
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
        $qb = getQueryBuilder();

        $result = $qb   ->select("battle")
                        ->from("Main:Battle", "battle")
                        ->where("battle.active = ?1")->setParameter(1, $onlyactive)
                        ->getQuery()->getResult();

        return $result;
    }
}
?>
