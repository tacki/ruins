<?php
/**
 * User Manager Class
 *
 * Class to manage User- and Characteraccounts
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Manager;

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * User Manager Class
 *
 * Class to manage User- and Characteraccounts
 * @package Ruins
 */
class User
{
    /**
     * Check User+Password
     * @param string $username Username to check
     * @param string $password Password to check
     * @return mixed UserID of the User if successful, else false
     */
    public static function checkPassword($username, $password)
    {
        $qb = getQueryBuilder();

        $result = $qb    ->select("user.id")
                         ->from("Entities\User", "user")
                         ->where("user.login = ?1")->setParameter(1, $username)
                         ->andWhere("user.password = ?2")->setParameter(2, md5($password))
                         ->getQuery()
                         ->getOneOrNullResult();

         if ($result) {
             return $result;
         } else {
             return false;
         }

     }

    /**
     * Get Character Name from ID
     * @param integer $charid ID of the Character
     * @param bool $btCode Return Charactername including the btCode
     * @return mixed Charactername (including btCode) if successful, else false
     */
    public static function getCharacterName($charid, $btCode=true)
    {
        $qb = getQueryBuilder();

        if ($btCode) {
            $result = $qb->select("char.displayname");
        } else {
            $result = $qb->select("char.name");
        }

        $result = $qb   ->from("Entities\Character", "char")
                        ->where("char.id = ?1")->setParameter(1, $charid)
                        ->getQuery()
                        ->getOneOrNullResult();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get Character ID from Name or Displayname
     * @param string $charactername
     * @return mixed CharacterID if successful, else false
     */
    public static function getCharacterID($charactername)
    {
        // purge existing btCode-Tags
        $charactername = \btCode::purgeTags($charactername);

        $qb = getQueryBuilder();

        $result = $qb   ->select("char.id")
                        ->from("Entities\Character", "char")
                        ->where("char.name LIKE ?1")->setParameter(1, $charactername)
                        ->getQuery()
                        ->getOneOrNullResult();

        if ($result) {
            return $result['id'];
        } else {
            return false;
        }
    }

    /**
     * Determine Character Type
     * @param int $charid Character ID
     * @return mixed CharacterType as string if successful, else false
     */
    public static function getCharacterType($charid)
    {
        // check type of character
        $qb = getQueryBuilder();

        $result = $qb   ->select("char.type")
                        ->from("Entities\Character", "char")
                        ->where("char.id = ?1")->setParameter(1, $charid)
                        ->getQuery()
                        ->getOneOrNullResult();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Determine Character Race
     * @param int $charid Character ID
     * @return mixed CharacterRace as string if successful, else false
     */
    public static function getCharacterRace($charid)
    {
        // check type of character
        $qb = getQueryBuilder();

        $result = $qb   ->select("char.race")
                        ->from("Entities\Character", "char")
                        ->where("char.id = ?1")->setParameter(1, $charid)
                        ->getQuery()
                        ->getOneOrNullResult();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get List of Characters for given User
     * @param int $userid User ID
     * @return mixed Array of Character ID's if successful, else false
     */
    public static function getUserCharactersList($userid)
    {
        $qb = getQueryBuilder();

        $result = $qb   ->select("char")
                        ->from("Entities\Character", "char")
                        ->where("char.user = ?1")->setParameter(1, $userid)
                        ->getQuery()
                        ->getResult();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get Complete List of Characters
     * @param array $fields Characterdata to include in the Result
     * @param string $order Order by Database Column
     * @param string $orderDir "ASC" for ascending, "DESC" for descending
     * @return array 2-dimensional Array
     */
    public static function getCharacterList($fields=false, $order="id", $orderDir="ASC", $onlineonly=false)
    {
        $qb = getQueryBuilder();

        if (is_array($fields)) {
            foreach($fields as $key=>$column) {
                $fields[$key] = "char." . $column;
            }
            $qb->select($fields);
        } else {
            $qb->select("char." . $fields);
        }

        $qb    ->from("Entities\Character", "char");

        if ($onlineonly) {
            global $config;

            $qb ->andWhere("char.loggedin = 1")
                ->andWhere("char.lastpagehit > ?2")
                ->setParameter(2, new \DateTime("-".$config->get("connectiontimeout", 15)." minutes"));
        }

        $qb ->orderBy("char.".$order, $orderDir);

        $result = $qb->getQuery()->getResult();

        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get List of online Characters
     * @return array Array of Characternames if successful
     */
    public static function getCharactersOnline()
    {
        $characters = self::getCharacterList(array("displayname"), "id", "ASC", true);

        $result = array();

        foreach ($characters as $character) {
            $result[] = $character->displayname;
        }

        return $result;
    }

    /**
     * Get List of online Characters currently at the given Place
     * @param string $place The place to check (example: ironlance/citysquare)
     * @return Array of Characternames if successful
     */
    public static function getCharactersAt($place)
    {
        global $config;

        $qb = getQueryBuilder();

        $result = $qb    ->select("char.displayname")
                         ->from("Entities\Character", "char")
                         ->andWhere("char.current_nav LIKE ?1")->setParameter(1, "page=".$place."%")
                         ->andWhere("char.lastpagehit > ?2")
                         ->setParameter(2, new \DateTime("-".$config->get("connectiontimeout", 15)." minutes"))
                         ->getQuery()
                         ->getResult();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
}
?>
