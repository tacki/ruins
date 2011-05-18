<?php
/**
 * btCode2 Class
 *
 * btCode2 Code Class to decode the color+special character code of ruins
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: btcode.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * btCode2 Class
 *
 * btCode2 Code Class to decode the color+special character code of ruins
 * @package Ruins
 */
class btCode2
{

    const BTID          = "`";
    const BTID_CLOSER   = ":";

    const EXCLUDESUBID  = "x";

    const CONTROLTAG           = "CONTROLTAG";
    const CONTROLTAG_LENGTH    = 1;

    const COLORTAG          = "COLORTAG";
    const COLORTAG_LENGTH   = 2;
    const COLORSUBID        = "#";
    const COLORBACKSUBID    = "~";

    public static function decode($decodestring)
    {
        $opentags = array();
        $offset = 0;

        // Handle Exclude-Tags
        if (strpos($decodestring, self::BTID.self::EXCLUDESUBID) !== false) {
            // exclude-tag present
            $result = explode("`x", $decodestring);
            $res = "";
            for ($i=0; $i<=count($result); $i=$i+2) {
                $res .= self::decode($result[$i]);
                $res .= $result[$i+1];
            }

            return $res;
        }

        // Handle Normal Tags
        while ( (($position = strpos($decodestring, self::BTID, $offset)) !== false) ) {
            self::_translateControlTags($decodestring, $opentags);
            self::_translateColorTags($decodestring, $opentags);

            $offset = $position+1;
        }

        // Close still open tags
        self::_closeOpenTags($decodestring, $opentags);

        return ($decodestring);
    }

    public static function decodeToCSSColorClass($decodestring)
    {
        $offset = 0;

        while ( (($position = strpos($decodestring, self::BTID, $offset)) !== false) ) {
            $tagid = substr($decodestring, $position+1, 1);

            $search = self::_getRegex("color");

            if (preg_match_all($search, $decodestring, $matches)) {
                // Result is in $matches (2-dimensional array)

                foreach ($matches[0] as $result) {
                    // fetch Colorcode
                    $colorcode = substr($result, 2, self::COLORTAG_LENGTH);

                    // Replace the Tag
                    $decodestring = preg_replace("/".preg_quote($result)."/",
                                                 str_replace("XXX", $colorcode, self::_getTag($tagid, "class")),
                                                 $decodestring);
                }
            }

            $offset = $position+1;
        }

        return ($decodestring);
    }

    /**
     * Protect a given string from being decoded
     * @param string $nodecodestring String to protect
     * @return string protected String
     */
    public static function exclude($nodecodestring)
    {
        return self::BTID.self::EXCLUDESUBID . $nodecodestring . self::BTID.self::EXCLUDESUBID;
    }

    /**
     * Purge btCode-tags
     * @param string $decodestring String to purge
     * @return string String without btCode-tags
     */
    public static function purgeTags($decodestring)
    {
        $searches = array (
                    // THE ORDER IS IMPORTANT!
                    // Color-Gradient
                    self::_getRegex("color-gradient"),
                    // Colors
                    self::_getRegex("color"),
                    // Control
                    self::_getRegex("control"),
        );

        // Remove Control-Tags
        $decodestring = preg_replace($searches, "", $decodestring);

        return $decodestring;
    }

    private static function _getTag($tagid, $state)
    {
        $tags = array (
                            // bold
                            "b" => array ( "open" => "<strong>", 				"close" => "</strong>" ),
                            // center
                            "c" => array ( "open" => "<div class='btcode_c'>",  "close" => "</div>" ),
                            // big
                            "g" => array ( "open" => "<big>", 					"close" => "</big>" ),
                            // italic
                            "i" => array ( "open" => "<em>", 					"close" => "</em>" ),
                            // newline
                            "n" => array ( "open" => "<br />", 					),

                            // normal color
                            self::COLORSUBID     => array ( "open" => "<span class='btcode_XXX'>", 	"close" => "</span>", "class" => 'btcode_XXX'),
                            // background color
                            self::COLORBACKSUBID => array ( "open" => "<span class='btcode_XXX_bg'>", "close" => "</span>" , "class" => 'btcode_XXX_bg'),
                        );

        if (isset($tags[$tagid][$state])) {
            return ($tags[$tagid][$state]);
        } else {
            return false;
        }
    }

    private static function _getRegex($type, $forceCloseTag=false, $addNextWord=false)
    {
        $regex = array (
                    // Color-Gradient
                    "color-gradient"         => "/".preg_quote(self::BTID) . "(".self::COLORSUBID."|".self::COLORBACKSUBID.")" .
                                                "[[:xdigit:]]{".self::COLORTAG_LENGTH."}-[[:xdigit:]]{".self::COLORTAG_LENGTH."}",


                    // Colors - closetag optional
                    "color"                  => "/".preg_quote(self::BTID) . "(".self::COLORSUBID."|".self::COLORBACKSUBID.")" .
                                                "[[:xdigit:]]{".self::COLORTAG_LENGTH."}",

                    // Control - closetag optional
                    "control"                => "/".preg_quote(self::BTID) .
                                                "[[:alpha:]]{".self::CONTROLTAG_LENGTH."}",
        );

        if (isset($regex[$type])) {
            $regexres = $regex[$type];
            if ($forceCloseTag) {
                $regexres .= preg_quote(self::BTID_CLOSER);
            } else {
                $regexres .= "(".preg_quote(self::BTID_CLOSER).")?";
            }

            if ($addNextWord) {
                $regexres .= "\s*\w+";
            }

            $regexres .= "/";
            return ($regexres);
        } else {
            return false;
        }
    }

    private static function _identifyTagType($tagid)
    {
        if ($tagid == self::COLORSUBID || $tagid == self::COLORBACKSUBID) {
            return self::COLORTAG;
        } elseif (preg_match("/^[[:alpha:]]{".self::CONTROLTAG_LENGTH."}$/", $tagid)) {
            return self::CONTROLTAG;
        } else {
            return false;
        }
    }

    private static function _translateControlTags(&$decodestring, array &$opentags)
    {
        // OPENCLOSE CONTROL TAGS WHICH RELATE ONLY TO THE NEXT WORD (example: `b:word)
        if (preg_match_all(self::_getRegex("control", true, true), $decodestring, $matches)) {
            // Result is in $matches (2-dimensional array)

            foreach ($matches[0] as $result) {
                 // Get TagID
                $tagid = substr($result, 1, 1);

                // Replace the Tag
                $replacement = preg_replace(self::_getRegex("control", true),
                                            self::_getTag($tagid, "open"),
                                            $result);

                // Add Closing Tag after the Word
                $replacement = $replacement . self::_getTag($tagid, "close");

                // Replace it inside $decodestring
                $decodestring = preg_replace("/".preg_quote($result)."/",
                                             $replacement,
                                             $decodestring,
                                             1);
            }

        // OPENING/CLOSING CONTROL TAG (example: `b) - same tag closes
        } elseif (preg_match_all(self::_getRegex("control"), $decodestring, $matches)) {
            // Result is in $matches (2-dimensional array)

            foreach ($matches[0] as $result) {
                 // Get TagID
                $tagid = substr($result, 1, 1);

                if (($found = array_search($tagid, $opentags)) !== false) {
                    // Close already opened Tag
                    $replacetag = self::_getTag($tagid, "close");
                    unset($opentags[$found]);
                } else {
                    // Opener Tag
                    $replacetag = self::_getTag($tagid, "open");

                    // Add to $opentags if a close-Tag exists
                    if (self::_getTag($tagid, "close")) {
                        $opentags[] = $tagid;
                    }
                }

                // Replace the Tag
                $replacement = preg_replace(self::_getRegex("control"),
                                            $replacetag,
                                            $result);

                // Replace the Tag inside $decodestring
                $decodestring = preg_replace("/".preg_quote($result)."/",
                                             $replacement,
                                             $decodestring,
                                             1);
            }

        }

    }

    private static function _translateColorTags(&$decodestring, array &$opentags)
    {

        // COLOR GRADIENT FOR THE NEXT WORD (example: `#50-5f:word)
        if (preg_match_all(self::_getRegex("color-gradient", true, true), $decodestring, $matches)) {
            // Result is in $matches (2-dimensional array)

            foreach ($matches[0] as $result) {
                // Get TagID
                $tagid = substr($result, 1, 1);

                // fetch Colorcodes
                $colorcode1 = substr($result, 2, self::COLORTAG_LENGTH);
                $colorcode2 = substr($result, 2+self::COLORTAG_LENGTH+1, self::COLORTAG_LENGTH);

                // fetch the Word
                preg_match("/".preg_quote(self::BTID_CLOSER)."\s*\w+/", $result, $word);
                $word = substr($word[0], 1); // strip leading Closer

                // Replace the Tag inside $decodestring
                $decodestring = preg_replace(self::_getRegex("color-gradient", true, true),
                                             self::_createColorGradient($word, $colorcode1, $colorcode2),
                                             $decodestring);

                // Translate the new tags
                self::_translateColorTag($decodestring, $tagid, $opentags);
            }

        // OPENCLOSE COLOR TAGS WHICH RELATES ONLY TO THE NEXT WORD (example: `#2f:word)
        } elseif (preg_match_all(self::_getRegex("color", true, true), $decodestring, $matches)) {
            // Result is in $matches (2-dimensional array)

            foreach ($matches[0] as $result) {
                // Get TagID
                $tagid = substr($result, 1, 1);

                // fetch Colorcode
                $colorcode = substr($result, 2, self::COLORTAG_LENGTH);

                // Replace the Tag
                $replacement = preg_replace(self::_getRegex("color", true),
                                            str_replace("XXX", $colorcode, self::_getTag($tagid, "open")),
                                            $result);

                // Add Closing Tag after the Word
                $replacement = $replacement . self::_getTag($tagid, "close");

                // Replace it inside $decodestring
                $decodestring = preg_replace("/".preg_quote($result)."/",
                                             $replacement,
                                             $decodestring,
                                             1);
            }

        // OPENING/CLOSING COLOR TAG (example: `#a9) - same tag closes and reopens
        } elseif (preg_match_all(self::_getRegex("color"), $decodestring, $matches)) {
            // Result is in $matches (2-dimensional array)

            foreach ($matches[0] as $result) {
                // Get TagID
                $tagid = substr($result, 1, 1);

                $replacetag = "";
                if (($found = array_search($tagid, $opentags)) !== false) {
                    // Close already opened Tag
                    $replacetag .= self::_getTag($tagid, "close");
                    unset($opentags[$found]);
                }

                // Opener Tag
                $replacetag .= self::_getTag($tagid, "open");

                // Add to $opentags if an close-Tag exists
                if (self::_getTag($tagid, "close")) {
                    $opentags[] = $tagid;
                }

                // fetch Colorcode
                $colorcode = substr($result, 2, self::COLORTAG_LENGTH);

                // Replace the Tag
                $replacement = preg_replace(self::_getRegex("color"),
                                            str_replace("XXX", $colorcode, $replacetag),
                                            $result);

                // Replace the Tag inside $decodestring
                $decodestring = preg_replace("/".preg_quote($result)."/",
                                             $replacement,
                                             $decodestring,
                                             1);
            }
        }
    }

    private static function _createColorGradient($word, $color1, $color2)
    {
        if (!ctype_xdigit($color1) || !ctype_xdigit($color2)) {
            return $word;
        }

        $colordiff  = hexdec($color2) - hexdec($color1);
        $wordlength = strlen($word);

        // calculate stepsize
        $stepsize = $colordiff / $wordlength;

        $result = "";
        $curcol = hexdec($color1);
        for ($i = 0; $i<$wordlength; $i++) {
            // Generate Gradient (colorcode before every letter)
            $result .= self::BTID . self::COLORSUBID . dechex(round($curcol)) . substr($word, $i, 1);
            $curcol = $curcol + $stepsize;
        }

        return $result;
    }

    private static function _closeOpenTags(&$decodestring, &$opentags)
    {
        foreach ($opentags as $key => $opentag) {
            $decodestring = $decodestring . self::_getTag($opentag, "close");
            unset ($opentags[$key]);
        }
    }
}
?>
