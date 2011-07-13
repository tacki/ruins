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
    Main\Entities\UserSetting;

/**
 * User Repository
 * @package Ruins
 */
class UserRepository extends Repository
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
        if (!($createUser = $this->findOneByLogin($username))) {
            $createUser = new User;
            $createUser->login = $username;
            $createUser->password = $this->hashPassword($password);
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
        $user = $this->findOneByLogin($username);

        if ($user && $this->hashPassword($password, $user->password) === $user->password) {
            return $user;
        } else {
            return false;
        }

    }

    /**
     * Generate secure, crypted Hash
     * @param string $password
     * @param string $salt Keep empty to generate a new Hash
     * @return string
     */
    public function hashPassword($password, $salt=false)
    {
        if ($salt === false) {
            // Generate a new Hash
            $randomNumber = pow(mt_rand(mt_getrandmax()/2, mt_getrandmax()), 2);
            $randomString = base_convert($randomNumber, 10, 36);

            if (CRYPT_SHA512 == 1) {
                return crypt($password, '$6$'.$randomString.'$');
            }
            if (CRYPT_SHA256 == 1) {
                return crypt($password, '$5$'.$randomString.'$');
            }
            if (CRYPT_BLOWFISH == 1) {
                return crypt($password, '$2a$'.$rand(10,30).'$'.$randomstring.'$');
            }
            if (CRYPT_MD5 == 1) {
                return crypt($password, '$1$'.$randomstring.'$');
            }
        }

        return crypt($password, $salt);
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