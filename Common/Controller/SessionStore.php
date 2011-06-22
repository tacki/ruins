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

        unset ($_SESSION[$sessionOption]);
    }

    /**
     * Create Session Object
     * @return bool true if Session Object already exists, else false
     */
    private static function _sanitize()
    {
        if (strlen(session_id()) == 0) {
            session_start();
        }

        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = array();
        }
    }
}
?>
