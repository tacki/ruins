<?php
/**
 * Global Functions
 *
 * Functions worldwide useable:D
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: global.func.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Checks if the given data is a serialized String
 * @param mixed $data Check if this Variable is a serialized String
 * @return bool true if $data is a serialized string, else false
 */
function is_serialized($data)
{
    if ( !is_string($data) ) {
        // if it isn't a string, it isn't serialized
           return false;
    }
    $data = trim($data);
    if ( preg_match("/^[adobis]:[0-9]+:.*[;}]/si", $data) ) {
        // this should fetch all legitimately serialized data
           return true;
    }

    return false;
}

/**
 * Checks if the given string consists of letters only
 * @param string $data The given string
 * @return bool true if $data consists of letters only, else false
 */
function is_alpha($data)
{
    if (preg_match("/^[[:alpha:]]+$/", $data)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Recursively strip all slashes
 * @param mixed $value Array or String to strip slashes from
 * @return mixed Given Array or String without slashes
 */
function stripslashes_deep($value)
{
    if (is_array($value)) {
        foreach($value as $k => $v) {
            $return[$k] = stripslashes_deep($v);
        }
    } elseif (isset($value) && is_string($value)) {
        $return = stripslashes($value);
    }

    return $return;
}

/**
 * Returns the currently active Output Object (prefers $page)
 * @return OutputObject Instance of OutputObject
 */
function getOutputObject()
{
    global $page;
    global $popup;

    if ($page instanceof OutputObject) {
        $outputobject =	$page;
    } elseif ($popup instanceof OutputObject) {
        $outputobject = $popup;
    } else {
        $outputobject = false;
    }

    return $outputobject;
}

/**
 * Return current Timestamp
 * @return float Timestamp
 */
function getMicroTime()
{
    return microtime(true);
}

/**
 * Generate Random String
 * @var int $length Length of the Random String
 * @var bool $uppercase Use only Uppercase Strings
 * @var bool $specialchars Use Specialchars
 * @var bool $removesimilar Remove possible unreadable Characters 0,O,Q,l,I,..
 * @return string generated, random String
 */

function generateRandomString($length=5, $uppercase=false, $specialchars=true, $removesimilar=false)
{
    $randomString = "";
    $similarChars = array(0,"O","Q","l","I","J");

    $chars = array();
    // define the characters to use
    // Numbers
    $chars = array_merge($chars, range(0, 9));

    // Characters
    if (!$uppercase) {
            $chars = array_merge($chars, range('a', 'z')); // Letters a-z
    }
    $chars = array_merge($chars, range('A', 'Z')); // Letters A-Z

    // Special Characters
    if ($specialchars) {
        $chars = array_merge($chars, array('#','&','@','$','_','%','?','+')); // Special Chars
    }

    if ($removesimilar) {
        $chars = array_diff($chars, $similarChars);
    }

    // Shuffle and re-index the Charlist
    shuffle($chars);

    for ($i=1; $i<=$length; $i++)
    {
        $charnr	= mt_rand(0, count($chars)-1);
        $char 	= $chars[$charnr];

        $randomString .= $char;
    }

    return $randomString;
}

/**
 * Make json more readable
 * original from damon1977 at gmail dot com (php forum)
 * @param string $json json formated string
 * @param book $html Output in HTML
 * @return string Nice formated json string
 */
function jsonReadable($json, $html=FALSE) {
    $tabcount = 0;
    $result = '';
    $inquote = false;
    $ignorenext = false;

    if ($html) {
        $space = " ";
        $tab = "&nbsp;&nbsp;&nbsp;&nbsp;";
        $newline = "<br/>";
    } else {
        $space = " ";
        $tab = "\t";
        $newline = "\r\n";
    }

    for($i = 0; $i < strlen($json); $i++) {
        $char = $json[$i];

        if ($ignorenext) {
            $result .= $char;
            $ignorenext = false;
        } else {
            switch($char) {
                case '{':
                    $tabcount++;
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '}':
                    $tabcount--;
                    $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                    break;
                case '[':
                    $tabcount++;
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case ']':
                    $tabcount--;
                    $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                    break;
                case ':':
                    $result .= $space . $char . $space;
                    break;
                case ',':
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '"':
                    $inquote = !$inquote;
                    $result .= $char;
                    break;
                case '\\':
                    if ($inquote) $ignorenext = true;
                    $result .= $char;
                    break;
                default:
                    $result .= $char;
            }
        }
    }

    return $result;
}

/**
 * Generate a new UniqueID
 */
function generateUniqueID()
{
    $uniqueID = md5(microtime());

    return $uniqueID;
}

/**
 * Returns the "true" IP address of the current request
 *
 * @return string the ip of the user
 */
function getRequestTrueIP()
{
    global $REMOTE_ADDR, $HTTP_CLIENT_IP;
    global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
    global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;

    // Get some server/environment variables values
    if (empty($REMOTE_ADDR)) {
        if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        }
        else if (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $_ENV['REMOTE_ADDR'];
        }
        else if (@getenv('REMOTE_ADDR')) {
            $REMOTE_ADDR = getenv('REMOTE_ADDR');
        }
    }

    if (empty($HTTP_CLIENT_IP)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_CLIENT_IP'])) {
            $HTTP_CLIENT_IP = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_CLIENT_IP'])) {
            $HTTP_CLIENT_IP = $_ENV['HTTP_CLIENT_IP'];
        }
        else if (@getenv('HTTP_CLIENT_IP')) {
            $HTTP_CLIENT_IP = getenv('HTTP_CLIENT_IP');
        }
    }

    if (empty($HTTP_X_FORWARDED_FOR)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
            $HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];
        }
        else if (@getenv('HTTP_X_FORWARDED_FOR')) {
            $HTTP_X_FORWARDED_FOR = getenv('HTTP_X_FORWARDED_FOR');
        }
    }

    if (empty($HTTP_X_FORWARDED)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED'])) {
            $HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];
        }
        else if (@getenv('HTTP_X_FORWARDED')) {
            $HTTP_X_FORWARDED = getenv('HTTP_X_FORWARDED');
        }
    }

    if (empty($HTTP_FORWARDED_FOR)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED_FOR'])) {
            $HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];
        }
        else if (@getenv('HTTP_FORWARDED_FOR')) {
            $HTTP_FORWARDED_FOR = getenv('HTTP_FORWARDED_FOR');
        }
    }

    if (empty($HTTP_FORWARDED)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED'])) {
            $HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];
        }
        else if (@getenv('HTTP_FORWARDED')) {
            $HTTP_FORWARDED = getenv('HTTP_FORWARDED');
        }
    }

    if (empty($HTTP_VIA)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_VIA'])) {
            $HTTP_VIA = $_SERVER['HTTP_VIA'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_VIA'])) {
            $HTTP_VIA = $_ENV['HTTP_VIA'];
        }
        else if (@getenv('HTTP_VIA')) {
            $HTTP_VIA = getenv('HTTP_VIA');
        }
    }

    if (empty($HTTP_X_COMING_FROM)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_X_COMING_FROM'])) {
            $HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];
        }
        else if (@getenv('HTTP_X_COMING_FROM')) {
            $HTTP_X_COMING_FROM = getenv('HTTP_X_COMING_FROM');
        }
    }

    if (empty($HTTP_COMING_FROM)) {
        if (!empty($_SERVER) && isset($_SERVER['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];
        }
        else if (!empty($_ENV) && isset($_ENV['HTTP_COMING_FROM'])) {
            $HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];
        }
        else if (@getenv('HTTP_COMING_FROM')) {
            $HTTP_COMING_FROM = getenv('HTTP_COMING_FROM');
        }
    }

    // Gets the default ip sent by the user
    if (!empty($REMOTE_ADDR)) {
        $direct_ip = $REMOTE_ADDR;
    }

    // Gets the proxy ip sent by the user
    $proxy_ip     = '';
    if (!empty($HTTP_X_FORWARDED_FOR)) {
        $proxy_ip = $HTTP_X_FORWARDED_FOR;
    } else if (!empty($HTTP_X_FORWARDED)) {
        $proxy_ip = $HTTP_X_FORWARDED;
    } else if (!empty($HTTP_FORWARDED_FOR)) {
        $proxy_ip = $HTTP_FORWARDED_FOR;
    } else if (!empty($HTTP_FORWARDED)) {
        $proxy_ip = $HTTP_FORWARDED;
    } else if (!empty($HTTP_VIA)) {
        $proxy_ip = $HTTP_VIA;
    } else if (!empty($HTTP_X_COMING_FROM)) {
        $proxy_ip = $HTTP_X_COMING_FROM;
    } else if (!empty($HTTP_COMING_FROM)) {
        $proxy_ip = $HTTP_COMING_FROM;
    }

    // Returns the true IP if it has been found, else ...
    if (empty($proxy_ip)) {
        // True IP without proxy
        return $direct_ip;
    } else {
        $is_ip = ereg('^([0-9]{1,3}.){3,3}[0-9]{1,3}', $proxy_ip, $regs);

        if ($is_ip && (count($regs) > 0)) {
            // True IP behind a proxy
            return $regs[0];
        } else {

            if (empty($HTTP_CLIENT_IP)) {
                // Can't define IP: there is a proxy but we don't have
                // information about the true IP
                return "(unbekannt) " . $proxy_ip;
            } else {
                // better than nothing
                return $HTTP_CLIENT_IP;
            }
        }
    }
}

/**
 * Classes Autoloader - includes all Class-Files on the Fly
 * @param string $class_name Name of the class to load
 */
function ruinsAutoload($classname) {
    // Ower own Classes are all lowercase
    $classname_lc			= strtolower($classname);

    // Underscored Classnames
    // HTML_Table => HTML/Table.php
    $classname_elements		= explode("_", $classname);
    $classname_elements_lc	= explode("_", strtolower($classname));

    // Initialize SessionCache
    // Makes autoloading a lot faster, cause we don't have to check the filesystem everytime
    require_once(DIR_INCLUDES."classes/sessionstore.class.php");
    if ($fromcache = SessionStore::readCache("__autoload_".$classname)) {
        //require_once($fromcache);
        //return true;
    }

   if (file_exists(DIR_INCLUDES."classes/".$classname_lc.".class.php")) {
        // Our own Classes
        SessionStore::writeCache("__autoload_".$classname, DIR_INCLUDES."classes/".$classname_lc.".class.php");
        require_once(DIR_INCLUDES."classes/".$classname_lc.".class.php");
    } elseif (file_exists(DIR_INCLUDES."interfaces/".$classname_lc.".interface.php")) {
        // Our own Interfaces
        SessionStore::writeCache("__autoload_".$classname, DIR_INCLUDES."interfaces/".$classname_lc.".interface.php");
        require_once(DIR_INCLUDES."interfaces/".$classname_lc.".interface.php");
    } elseif (file_exists(DIR_BASE."modules/".$classname_lc."/".$classname_lc.".basemod.php")) {
        // Our own BaseModules
        SessionStore::writeCache("__autoload_".$classname, DIR_BASE."modules/".$classname_lc."/".$classname_lc.".basemod.php");
        require_once(DIR_BASE."modules/".$classname_lc."/".$classname_lc.".basemod.php");
    }
}
?>
