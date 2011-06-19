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
namespace Main\Manager;
use Common\Controller\BtCode,
    Main\Entities;

/**
 * Global Includes
 */
require_once(DIR_BASE."main.inc.php");

/**
 * User Manager Class
 *
 * Class to manage User- and Characteraccounts
 * @package Ruins
 */
class User
{

    /**
     * Create Character
     * @param string $charactername
     * @param Entities\User $user
     * @return Entities\Character
     */
    public static function createCharacter($charactername, Entities\User $user=NULL)
    {
        global $em;

        if (!($createCharacter = $em->getRepository("Main:Character")->findOneByName($charactername))) {
            $createCharacter = new Entities\Character;
            $createCharacter->name = $charactername;
            $createCharacter->displayname = $charactername;
            if ($user) $createCharacter->user = $user;
            $em->persist($createCharacter);
        }

        return $createCharacter;
    }

    /**
    * Create User
    * @param string $username
    * @param string $password
    * @param Entities\Character $defaultCharacter
    * @return Entities\User
    */
    public static function createUser($username, $password, Entities\Character $defaultCharacter=NULL)
    {
        global $em;

        if (!($createUser = $em->getRepository("Main:User")->findOneByLogin($username))) {
            $createUser = new Entities\User;
            $createUser->login = $username;
            $createUser->password = md5($password);
            if ($defaultCharacter) $createUser->character = $defaultCharacter;
            $createUser->settings  = self::createUserSettings($createUser);
            $em->persist($createUser);
        }

        return $createUser;
    }

    /**
     * Create User Settings
     * @param Entities\User $user
     * @return Entities\UserSetting
     */
    public static function createUserSettings(Entities\User $user)
    {
        global $em;

        $createSettings = new Entities\UserSetting;
        $createSettings->user = $user;
        $em->persist($createSettings);

        return $createSettings;
    }

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
                         ->from("Main:User", "user")
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

        $result = $qb   ->from("Main:Character", "char")
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
        $charactername = BtCode::purgeTags($charactername);

        $qb = getQueryBuilder();

        $result = $qb   ->select("char.id")
                        ->from("Main:Character", "char")
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
                        ->from("Main:Character", "char")
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
                        ->from("Main:Character", "char")
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
     * @param Entities\User $user User Object
     * @return mixed Array of Character Objects if successful, else false
     */
    public static function getUserCharactersList(Entities\User $user)
    {
        $qb = getQueryBuilder();

        $result = $qb   ->select("char")
                        ->from("Main:Character", "char")
                        ->where("char.user = ?1")->setParameter(1, $user)
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

        $qb    ->from("Main:Character", "char");

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
            $result[] = $character['displayname'];
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
        global $config, $user;

        $qb = getQueryBuilder();

        $result = $qb    ->select("char.displayname")
                         ->from("Main:Character", "char")
                         ->andWhere("char.current_nav LIKE ?1")->setParameter(1, "page=".$place."%")
                         ->andWhere("char.lastpagehit > ?2")
                         ->setParameter(2, new \DateTime("-".$config->get("connectiontimeout", 15)." minutes"))
                         ->andWhere("char.user != ?3")->setParameter(3, $user)
                         ->getQuery()
                         ->getResult();

        $characterlist = array($user->character->displayname);

        if ($result) {
            foreach ($result as $entry) {
                $characterlist[] = $entry['displayname'];
            }
        }

        return $characterlist;
    }
}
?>
