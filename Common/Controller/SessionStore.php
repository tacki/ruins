<?php
/**
 * Session Store Class
 *
 * Manage all Sessionhandling
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;
use Common\Controller\Error;

/**
 * Session Store Class
 *
 * Manage all Sessionhandling
 * @package Ruins
 */
class SessionStore
{

    /**
     * Put a Value into the Session
     * @param string $sessionOption Name of the Session Option
     * @param mixed $value Value of the Session Option
     */
    public static function set($sessionOption, $value)
    {
        self::_sanitize();

        if ($sessionOption === "cache" || $sessionOption === "cachevalidity") {
            throw new Error("'cache' and 'cachevalidity' are not allowed as sessionOption using SessionStore::set()");
        }

        $_SESSION[$sessionOption] = $value;
    }

    /**
     * Get a Value out of the Session
     * @param string $sessionOption Name of the Session Option
     * @return mixed Value of the requested Session Option or NULL if the Option doesn't exist
     */
    public static function get($sessionOption)
    {
        self::_sanitize();

        if ($sessionOption === "cache" || $sessionOption === "cachevalidity") {
            throw new Error("'cache' and 'cachevalidity' are not allowed as sessionOption using SessionStore::get()");
        }

        if (array_key_exists($sessionOption, $_SESSION)) {
            $result = $_SESSION[$sessionOption];
        } else {
            return false;
        }

        return $result;
    }

    /**
     * Remove a Value out of the Session
     * @param string $sessionOption Name of the Session Option
     */
    public static function remove($sessionOption)
    {
        self::_sanitize();

        if ($sessionOption === "cache" || $sessionOption === "cachevalidity") {
            throw new Error("'cache' and 'cachevalidity' are not allowed as sessionOption using SessionStore::remove()");
        }

        unset ($_SESSION[$sessionOption]);
    }

    /**
     * Add data to the Session Cache
     * @param string $cachename Name of the Cache to add
     * @param mixed $data Data to add
     * @param int $validity Cache is 'x' seconds valid (or 'page' for the duration of 1 page)
     */
    public static function writeCache($cachename, $data, $validity=false)
    {
        self::_sanitize();

        if (is_object($data)) {
            $data = serialize($data);
        }

        $_SESSION['cache'][$cachename] = $data;

        if (strtolower($validity) == "page") {
            $_SESSION['cachevalidity'][$cachename]	= "page";
        } elseif (is_numeric($validity)) {
            $_SESSION['cachevalidity'][$cachename] 	= time() + $validity;
        }
    }

    /**
     * Read data from the Session Cache
     * @param string $cachename Name of the Cache to read
     * @return mixed Data Content
     */
    public static function readCache($cachename)
    {
        self::_sanitize();

        if (array_key_exists($cachename, $_SESSION['cachevalidity']) &&
                is_numeric($_SESSION['cachevalidity'][$cachename]) &&
                $_SESSION['cachevalidity'][$cachename] < time()) {
            self::pruneCache($cachename);
            return false;
        }

        if (array_key_exists($cachename, $_SESSION['cache'])) {
            if (is_serialized($_SESSION['cache'][$cachename])) {
                return unserialize($_SESSION['cache'][$cachename]);
            } else {
                return $_SESSION['cache'][$cachename];
            }
        } else {
            return false;
        }
    }

    /**
     * Prune Cache
     * @param string|bool $cachename Name of the Cache to prune. Set to false to prune the whole cache
     */
    public static function pruneCache($cachename=false)
    {
        self::_sanitize();

        if ($cachename) {

            if (strpos($cachename, "*") !== false) {
                // Wildcard * is used
                // This allowes to prune Cache
                $cachepart = substr($cachename, 0, strpos($cachename, "*"));

                $cachepartlength = strlen($cachepart);

                foreach($_SESSION['cache'] as $cacheentry=>$value) {
                    if (substr($cacheentry, 0, $cachepartlength) == $cachepart) {
                        unset($_SESSION['cache'][$cacheentry]);
                        if (isset($_SESSION['cachevalidity'][$cacheentry])) {
                            unset ($_SESSION['cachevalidity'][$cacheentry]);
                        }
                    }
                }
            } else {
                if (isset($_SESSION['cache'][$cachename])) {
                    unset ($_SESSION['cache'][$cachename]);
                }
                if (isset($_SESSION['cachevalidity'][$cachename])) {
                    unset ($_SESSION['cachevalidity'][$cachename]);
                }
            }
        } else {
            $_SESSION['cache'] 			= array();
            $_SESSION['cachevalidity'] 	= array();
        }
    }

    /**
     * Prune Cache which is only valid for 1 Page
     * @return unknown_type
     */
    private static function _prunePageCache()
    {
        if (isset($_SESSION['cachevalidity']) && is_array($_SESSION['cachevalidity']) ) {
            foreach ($_SESSION['cachevalidity'] as $cachename=>$cachevalidity) {
                if ($cachevalidity == 'page') {
                    self::pruneCache($cachename);
                }
            }
        }
    }

    /**
     * Create Session Object
     * @return bool true if Session Object already exists, else false
     */
    private static function _sanitize()
    {
        if (strlen(session_id()) == 0) {
            session_start();
            self::_prunePageCache();
        }

        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = array();
        }

        if (!isset($_SESSION['cache']) || !is_array($_SESSION['cache']) ||
                !isset($_SESSION['cachevalidity']) || !is_array($_SESSION['cachevalidity']) ) {
            $_SESSION['cache'] 			= array();
            $_SESSION['cachevalidity'] 	= array();
        }
    }
}
?>
