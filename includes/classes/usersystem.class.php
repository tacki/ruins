<?php
/**
 * UserSystem Class
 *
 * Class to manage User- and Characteraccounts
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: usersystem.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * UserSystem Class
 *
 * Class to manage User- and Characteraccounts
 * @package Ruins
 */
class UserSystem
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
                         ->from("Entities\User', 'user")
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
        if (!$result = SessionStore::readCache($charid . "_CharacterName_".$btCode)) {
            $qb = getQueryBuilder();

            if ($btCode) {
                $result = $qb->select("char.displayname");
            } else {
                $result = $qb->select("char.name");
            }

            $result = $qb   ->from("Entities\Character", "char")
                            ->where("char.id = ?1")->setParameter($charid)
                            ->getOneOrNullResult();

            if ($result) {
                SessionStore::writeCache($charid . "_CharacterName_".$btCode, $result);
            } else {
                return false;
            }
        }

        return $result;
    }

    /**
     * Get Character ID from Name or Displayname
     * @param string $charactername
     * @return mixed CharacterID if successful, else false
     */
    public static function getCharacterID($charactername)
    {
        // purge existing btCode-Tags
        $charactername = btCode::purgeTags($charactername);

        if (!$result = SessionStore::readCache($charactername . "_CharacterID ")) {
            $qb = getQueryBuilder();

            $result = $qb   ->select("char.id")
                            ->from("Entities\Character", "char")
                            ->where("char.name = ?1")->setParameter($charactername)
                            ->getOneOrNullResult();

            if ($result) {
                SessionStore::writeCache($charactername . "_CharacterID", $result);
            } else {
                return false;
            }
        }

        return $result;
    }

    /**
     * Determine Character Type
     * @param int $charid Character ID
     * @return mixed CharacterType as string if successful, else false
     */
    public static function getCharacterType($charid)
    {
        // check type of character
        if (!$result = SessionStore::readCache($charid . "_CharacterType")) {
            $qb = getQueryBuilder();

            $result = $qb   ->select("char.type")
                            ->from("Entities\Character", "char")
                            ->where("char.id = ?1")->setParameter($charid)
                            ->getOneOrNullResult();

            if ($result) {
                SessionStore::writeCache($charid . "_CharacterType", $result);
            } else {
                return false;
            }
        }

        return $result;
    }

    /**
     * Determine Character Race
     * @param int $charid Character ID
     * @return mixed CharacterRace as string if successful, else false
     */
    public static function getCharacterRace($charid)
    {
        // check type of character
        if (!$result = SessionStore::readCache($charid . "_CharacterRace")) {
            $qb = getQueryBuilder();

            $result = $qb   ->select("char.race")
                            ->from("Entities\Character", "char")
                            ->where("char.id = ?1")->setParameter($charid)
                            ->getOneOrNullResult();

            if ($result) {
                SessionStore::writeCache($charid . "_CharacterRace", $result);
            } else {
                return false;
            }
        }

        return $result;
    }

    /**
     * Get List of Characters for given User
     * @param int $userid User ID
     * @return mixed Array of Character ID's if successful, else false
     */
    public static function getUserCharactersList($userid)
    {
        if (!$result = SessionStore::readCache($userid . "_CharacterList")) {

            $qb = getQueryBuilder();

            $result = $qb   ->select("char.id")
                            ->from("Entities\Character", "char")
                            ->where("char.user = ?1")->setParameter($userid)
                            ->getResult();

            if ($result) {
                SessionStore::writeCache($userid . "_CharacterList", $result);
            } else {
                return false;
            }
        }

        return $result;
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
        if (!$result = SessionStore::readCache("CharacterList_".serialize($fields)."_".$order."_".$orderDesc."_".$onlineonly)) {
            $qb = getQueryBuilder();

            if (is_array($fields)) {
                foreach($fields as $key=>$column) {
                    $fields[$key] = "char." . $column;
                }
                $qb->select($fields);
            }

            $qb    ->from("Entities\Character", "char")
                   ->where("char.type != ?1")->setParameter(1, "npc");

            if ($onlineonly) {
                global $config;

                $qb ->andWhere("char.loggedin = 1")
                    ->andWhere("char.lastpagehit < ?2")
                    ->setParameter(2, new DateTime("-".$config->get("connectiontimeout", 15)." minutes"));
            }

            $qb    ->orderBy("char.".$order, $orderDir);

            $result = $qb->getQuery()->getResult();

/*
            $dbqt = new QueryTool();

            if (is_array($fields)) {
                $dbqt->select("id");

                foreach ($fields as $column) {
                    $dbqt->select($column);
                }
            } else {
                $dbqt->select("*");
            }

            $dbqt->from("characters");

            // Don't show NPC-characters
            $dbqt->where("type != ".$dbqt->quote("npc"));

            // Add OnlineCheck
            if ($onlineonly) {
                global $config;

                $dbqt->where("loggedin=1");
                $dbqt->where("lastpagehit>". $dbqt->quote(
                                                        MDB2_Date::unix2MDBstamp(
                                                            time() - ($config->get("connectiontimeout", 15)*60)
                                                        )
                                                     )
                            );
            }

            // Set Order
            $dbqt->order($order, $orderDesc);

            $result = $dbqt->exec()->fetchAll();
*/

            if ($result) {
                // CharacterLists-Cache is valid for 1 minute
                SessionStore::writeCache("CharacterList_".serialize($fields)."_".$order."_".$orderDesc."_".$onlineonly, $result, 60);
            } else {
                $result = array();
            }
        }

        return $result;
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
        if (!$result = SessionStore::readCache("CharactersAt_".$place)) {
            global $config;

            $qb = getQueryBuilder();

            $result = $qb    ->select("char.displayname")
                             ->from("Entities\Character", "char")
                             ->where("char.loggedin = 1")
                             ->andWhere("char.current_nav LIKE ?1")->setParameter(1, $place)
                             ->andWhere("char.lastpagehit < ?2")
                             ->setParameter(2, new DateTime("-".$config->get("connectiontimeout", 15)." minutes"))
                             ->getQuery()
                             ->getResult();
/*
            global $config;

            $dbqt = new QueryTool();

            $result = $dbqt	->select("displayname")
                            ->from("characters")
                            ->where("loggedin=1")
                            ->where("current_nav LIKE ".$dbqt->quote("page=$place%"))
                            ->where("lastpagehit>". $dbqt->quote(
                                                        MDB2_Date::unix2MDBstamp(
                                                            time() - ($config->get("connectiontimeout", 15)*60)
                                                        )
                                                     ))
                            ->exec()
                            ->fetchCol("displayname");
*/
            if ($result) {
                // CharactersAt-Cache is valid for 30 Seconds
                SessionStore::writeCache("CharactersAt_".$place, $result, 30);
            } else {
                $result = array();
            }
        }

        return $result;
    }
}
?>
