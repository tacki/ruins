<?php
/**
 * Registry Controller
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Controller;
use Doctrine\ORM\EntityManager;
use Ruins\Common\Controller\Config;
use Ruins\Common\Exceptions\Error;
use Ruins\Main\Entities\User;

/**
 * Class Name
 * @package Ruins
 */
class Registry
{
    /**
     * Registry Entries
     * @var array
     */
    protected static $entries = array();

    /**
     * Protected Names
     * @var array
     */
    protected static $protectedNames = array(
                                             "main.em",
                                             "main.config",
                                             "main.user",
                                            );

    /**
     * Get all Entries
     * @return array
     */
    public static function getAll()
    {
        return self::$entries;
    }

    /**
     * Get a specific Registry entry
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        return self::$entries[$name];
    }

    /**
     * Set a Registry entry (will overwrite)
     * @param string $name
     * @param mixed $value
     * @param bool $overwrite
     */
    public static function set($name, $value, $overwrite=false)
    {
        if (in_array($name, self::$protectedNames, true)) {
            throw new Error($name ." is a protected Registry Name. Please choose another or use corresponding methods.");
        }

        if (in_array($name, self::$entries, true) && !$overwrite) {
            throw new Error($name ." already exists. Use overwrite-Parameter to set again");
        }

        self::$entries[$name] = $value;
    }

    /**
     * Get all registered Registrynames
     * @return array
     */
    public static function getNames()
    {
        return array_keys(self::$entries);
    }

    /**
     * Set Entity Manager
     * @param EntityManager $em
     */
    public static function setEntityManager(EntityManager $em)
    {
        self::$entries['main.em'] = $em;
    }

    /**
     * Get Entity Manager
     * @return EntityManager
     */
    public static function getEntityManager()
    {
        return self::$entries['main.em'];
    }

    /**
     * Set Main Configuration
     * @param Config $config
     */
    public static function setMainConfig(Config $config)
    {
        self::$entries['main.config'] = $config;
    }

    /**
     * Get Main Configuration
     * @return Config
     */
    public static function getMainConfig()
    {
        return self::$entries['main.config'];
    }

    /**
     * Set current User
     * @param User $user
     */
    public static function setUser(User $user)
    {
        self::$entries['main.user'] = $user;
    }

    /**
     * Get current User
     * @return User
     */
    public static function getUser()
    {
        return self::$entries['main.user'];
    }
}