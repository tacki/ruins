<?php
/**
 * Caching Class
 *
 * Manage all Caches
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;
use Common\Controller\Registry;

/**
 * Caching Class
 *
 * Manage all Caches
 * @package Ruins
 */
class Cache
{
    /**
     * Retrieve the Cache Object
     * @return Doctrine\Common\Cache\AbstractCache Cache Object
     */
    public static function retrieve()
    {
        $config = Registry::getMainConfig();

        $cacheDriver = false;

        if ($config) {
            if ($config->getSub("option", "apc", 0)) {
                return self::_getCacheDriver("apc");
            } elseif ($config->get("option", "memcache", 0)) {
                return self::_getCacheDriver("memcache");
            } elseif ($config->get("option", "xcache", 0)) {
                return self::_getCacheDriver("xcache");
            }
        }

        if (!$cacheDriver) {
            return self::_getCacheDriver("arraycache");
        }
    }

   /**
    * Get Cache Driver
    * @param string $driver
    * @return object|false CacheDriver Object or false
    */
    private static function _getCacheDriver($driver)
    {
        switch ($driver) {
            default:
            case "arraycache":
                return new \Doctrine\Common\Cache\ArrayCache();

            case "apc":
                return new \Doctrine\Common\Cache\ApcCache();

            case "memcache":
                $memcache = new \Memcache();
                $memcache->connect('localhost', 11211);

                $cacheDriver = new \Doctrine\Common\Cache\MemcacheCache();
                $cacheDriver->setMemcache($memcache);
                return $cacheDriver;

            case "xcache":
                return new \Doctrine\Common\Cache\XcacheCache();
        }
    }
}