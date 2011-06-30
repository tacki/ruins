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
namespace Main\Repositories;
use Main\Entities\Character,
    Main\Entities\User,
    Main\Entities\UserSetting,
    Doctrine\ORM\EntityRepository;

/**
 * User Repository
 * @package Ruins
 */
class UserRepository extends EntityRepository
{
    /**
     * Create User
     * @param string $username
     * @param string $password
     * @param Main\Entities\Character $defaultCharacter
     * @return Main\Entities\User
     */
    public function create($username, $password, Character $defaultCharacter=NULL)
    {
        if (!($createUser = $this->getEntityManager()->getRepository("Main:User")->findOneByLogin($username))) {
            $createUser = new Entities\User;
            $createUser->login = $username;
            $createUser->password = md5($password);
            if ($defaultCharacter) $createUser->character = $defaultCharacter;
            $createUser->settings  = $this->createUserSettings($createUser);
            $this->getEntityManager()->persist($createUser);
        }

        return $createUser;
    }

    /**
     * Create User Settings
     * @param Main\Entities\User $user
     * @return Main\Entities\UserSetting
     */
    public function createUserSettings(User $user)
    {
        $createSettings = new UserSetting;
        $createSettings->user = $user;
        $this->getEntityManager()->persist($createSettings);

        return $createSettings;
    }

    /**
     * Check User+Password
     * @param string $username Username to check
     * @param string $password Password to check
     * @return Main\Entities\User The User if successful, else false
     */
    public function checkPassword($username, $password)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $result = $qb   ->select("user")
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
     * Get List of Characters for given User
     * @param Main\Entities\User $user User Object
     * @return mixed Array of Main\Entities\Character Objects if successful, else false
     */
    public function getCharacters(User $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

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
}