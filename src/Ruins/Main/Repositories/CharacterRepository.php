<?php
/**
 * User Repository
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Main\Repositories;
use DateTime;
use Ruins\Main\Entities\Character;
use Ruins\Main\Entities\User;
use Doctrine\DBAL\Types\Type;
use Ruins\Common\Controller\Registry;

/**
 * User Repository
 * @package Ruins
 */
class CharacterRepository extends Repository
{
    /**
     * Create Character
     * @param string $charactername
     * @param Ruins\Main\Entities\User $user
     * @return Ruins\Main\Entities\Character
     */
    public function create($charactername, User $user=NULL)
    {
        if (!($createCharacter = $this->findOneByName($charactername))) {
            $createCharacter = new Character;
            $createCharacter->name = $charactername;
            $createCharacter->displayname = $charactername;
            if ($user) $createCharacter->user = $user;
            $this->getEntityManager()->persist($createCharacter);
        }

        return $createCharacter;
    }

    /**
     * Get Complete List of Characters
     * @param array $fields Characterdata to include in the Result
     * @param string $order Order by Database Column
     * @param string $orderDir "ASC" for ascending, "DESC" for descending
     * @param bool $onlineonly Get only online Characters
     * @return array 2-dimensional Array
     */
    public function getList(array $fields, $order="id", $orderDir="ASC", $onlineonly=false)
    {
        $systemConfig = Registry::getMainConfig();

        $qb = $this->getEntityManager()->createQueryBuilder();

        // Select Fields
        foreach($fields as $key=>$column) {
            $fields[$key] = "char." . $column;
        }
        $qb ->select($fields)
            ->from("Main:Character", "char");

        // Only Online Characters
        if ($onlineonly) {
            $qb ->andWhere("char.loggedin = ?1")->setParameter(1, true, Type::BOOLEAN)
                ->andWhere("char.lastpagehit > ?2")
                ->setParameter(2, new DateTime("-".$systemConfig->get("connectiontimeout", 15)." minutes"));
        }

        // Order By
        $qb ->orderBy("char.".$order, $orderDir);

        $result = $qb->getQuery()->getResult();

        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get List of online Characters currently at the given Place
     * @param string $place The place to check (example: ironlance/citysquare)
     * @return Array of Characternames if successful
     */
    public function getListAtPlace($place)
    {
        $user = Registry::getUser();
        $systemConfig = Registry::getMainConfig();

        $qb = $this->getEntityManager()->createQueryBuilder();

        $result = $qb   ->select("char.displayname")
                        ->from("Main:Character", "char")
                        ->andWhere("char.current_nav LIKE ?1")->setParameter(1, "page/".$place."%")
                        ->andWhere("char.lastpagehit > ?2")
                        ->setParameter(2, new DateTime("-".$systemConfig->get("connectiontimeout", 15)." minutes"))
                        ->getQuery()
                        ->getResult();

        $characterlist = array($user->character->displayname);

        if ($result) {
            foreach ($result as $entry) {
                if ($entry['displayname'] !== $user->character->displayname) {
                    $characterlist[] = $entry['displayname'];
                }
            }
        }

        return $characterlist;
    }

    /**
     * Get current Battle of a given Character
     * @param Ruins\Main\Entities\Character $char Character to check
     * @return Ruins\Main\Entities\Battle
     */
    public function getBattle(Character $character)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

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
}