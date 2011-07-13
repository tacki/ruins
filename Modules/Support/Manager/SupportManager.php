<?php
/**
 * Support Manager
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Modules\Support\Manager;

/**
 * Support Manager
 */
class SupportManager
{
    /**
     * Generate Random String
     * @var int $length Length of the Random String
     * @var bool $uppercase Use only Uppercase Strings
     * @var bool $specialchars Use Specialchars
     * @var bool $removesimilar Remove possible unreadable Characters 0,O,Q,l,I,..
     * @return string generated, random String
     */
    public static function generateRandomString($length=5, $uppercase=false, $specialchars=true, $removesimilar=false)
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
}