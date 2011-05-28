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

    // Namespaces
    $namespaces		= explode("\\", strtolower($classname));

    // Initialize SessionCache
    // Makes autoloading a lot faster, cause we don't have to check the filesystem everytime
    require_once(DIR_INCLUDES."classes/sessionstore.class.php");
    if ($fromcache = SessionStore::readCache("__autoload_".$classname)) {
        require_once($fromcache);
        return true;
    }

    // Namespaces Autoloading
    if (count($namespaces) == 2) {
        if(file_exists(DIR_INCLUDES.$namespaces[0]."/".$namespaces[1].".class.php")) {
            require_once(DIR_INCLUDES.$namespaces[0]."/".$namespaces[1].".class.php");
        } elseif (file_exists(DIR_INCLUDES.$namespaces[0]."/".$namespaces[1].".interface.php")) {
            require_once(DIR_INCLUDES.$namespaces[0]."/".$namespaces[1].".interface.php");
        }
    } elseif (count($namespaces) == 3) {
        if(file_exists(DIR_INCLUDES.$namespaces[0]."/".$namespaces[1]."/".$namespaces[2]."/".".class.php")) {
            require_once(DIR_INCLUDES.$namespaces[0]."/".$namespaces[1]."/".$namespaces[2]."/".".class.php");
        }
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
    } elseif (count($classname_elements) == 3 && file_exists(DIR_INCLUDES_PEAR.$classname_elements[0]."/".$classname_elements[1]."/".$classname_elements[2].".php")) {
        // PEAR Sub-Subclasses
        SessionStore::writeCache("__autoload_".$classname, DIR_INCLUDES_PEAR.$classname_elements[0]."/".$classname_elements[1]."/".$classname_elements[2].".php");
        require_once(DIR_INCLUDES."external/pear/".$classname_elements[0]."/".$classname_elements[1]."/".$classname_elements[2].".php");
    } elseif (count($classname_elements) == 2 && file_exists(DIR_INCLUDES_PEAR.$classname_elements[0]."/".$classname_elements[1].".php")) {
        // PEAR Subclasses
        SessionStore::writeCache("__autoload_".$classname, DIR_INCLUDES."external/pear/".$classname_elements[0]."/".$classname_elements[1].".php");
        require_once(DIR_INCLUDES_PEAR.$classname_elements[0]."/".$classname_elements[1].".php");
    } elseif (file_exists(DIR_INCLUDES_PEAR.$classname.".php")) {
        // Primary PEAR Classes
        SessionStore::writeCache("__autoload_".$classname, DIR_INCLUDES."external/pear/".$classname.".php");
        require_once(DIR_INCLUDES_PEAR.$classname.".php");
    }
}
?>
