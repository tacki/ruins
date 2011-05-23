<?php
/**
 * Backtick Code Class
 *
 * Backtick Code Class to decode the color+special character code of ruins
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
 * Class Defines
 */
define("BTCODE_NUMERIC_LENGTH", 2);
define("BTCODE_ALPHA_LENGTH", 	1);

define("BTCODE_NUMERIC_VALID",	10);
define("BTCODE_NUMERIC_INVALID",11);
define("BTCODE_ALPHA_VALID",	20);
define("BTCODE_ALPHA_INVALID",	21);
define("BTCODE_UNKNOWN_INVALID",99);

define("BTCODE_LAYER_IDENTIFIER", "~");
define("BTCODE_LAYER_FOREGROUND", 10);
define("BTCODE_LAYER_BACKGROUND", 20);
define("BTCODE_LAYER_BACKGROUND_SUFFIX", "_bg");

define("BTCODE_EXCLUDE_TAG", "x");

/**
 * Backtick Code Class
 *
 * Backtick Code Class to decode the color+special character code of ruins
 * @package Ruins
 */
class btCode
{
    /**
     * Convert Backtick-Tags into <span>-Elements
     * @param string $decodestring String to convert
     * @return string Returns HTML-Code with converted Backtick-Tags
     */
    public static function decode($decodestring)
    {
        return self::_decodebtCode($decodestring);
    }

    /**
     * Convert Backtick-Tags into CSS-Classnames
     * @param string $decodestring String to convert
     * @return string Returns CSS-Classnames
     */
    public static function decoderaw($decodestring)
    {
        return self::_decodebtCode($decodestring, false);
    }

    /**
     * Protect a given string from being decoded
     * @param string $nodecodestring String to protect
     * @return string protected String
     */
    public static function exclude($nodecodestring)
    {
        return "`".BTCODE_EXCLUDE_TAG . $nodecodestring . "`".BTCODE_EXCLUDE_TAG;
    }

    /**
     * Purge btCode-tags
     * @param string $decodestring String to purge
     * @return string String without btCode-tags
     */
    public static function purgeTags($decodestring)
    {
        $digits = "";
        for ($i=0; $i<BTCODE_NUMERIC_LENGTH; $i++) {
            $digits .= "[[:digit:]]";
        }

        $alphas = "";
            for ($i=0; $i<BTCODE_ALPHA_LENGTH; $i++) {
            $alphas .= "[[:alpha:]]";
        }

        // remove numeric tags (colors)
        $decodestring = preg_replace("'[`]$digits'", "", $decodestring);

        // remove alpha tags (bold, center, big, ...)
        $decodestring = preg_replace("'[`]$alphas'", "", $decodestring);

        return $decodestring;
    }

    /**
     * Convert Backtick-Tags
     * @access private
     * @param string $decodestring String to convert
     * @param bool $spantags Include span-tags for colors
     * @return string Returns HTML-Code or simply the css-classes with converted Backtick-Tags
     */
    private static function _decodebtCode($decodestring, $spantags=true)
    {
        $tag 		= array("length"=>0,
                            "element"=>"",
                            "layer"=>BTCODE_LAYER_FOREGROUND);
        $tagsopen 	= array();
        $result 	= "";
        $excludetmp = "";

        while (!(($tag['position'] = strpos($decodestring,"`")) === false)) {

            switch (self::_identifyTag($decodestring, $tag)) {
                case BTCODE_NUMERIC_VALID:
                    if ($tag['layer'] == BTCODE_LAYER_FOREGROUND) {
                        $tag['element'] = substr($decodestring, $tag['position']+1, BTCODE_NUMERIC_LENGTH);
                        $append 		= substr($decodestring, 0, $tag['position']);
                        $newposition	= $tag['position'] + BTCODE_NUMERIC_LENGTH-1;
                    } else {
                        $tag['element'] = substr($decodestring, $tag['position']+1, BTCODE_NUMERIC_LENGTH) . BTCODE_LAYER_BACKGROUND_SUFFIX;
                        $append 		= substr($decodestring, 0, $tag['position']);
                        $newposition	= $tag['position'] + BTCODE_NUMERIC_LENGTH;
                    }
                    break;

                case BTCODE_ALPHA_VALID:
                    $tag['element']	= substr($decodestring, $tag['position']+1, BTCODE_ALPHA_LENGTH);
                    $append 		= substr($decodestring, 0, $tag['position']);
                    $newposition	= $tag['position'];
                    break;

                case BTCODE_NUMERIC_INVALID:
                    $tag['element']	= "invalid";
                    $append			= substr($decodestring, 0, $tag['position']);
                    $newposition	= $tag['position'] + $tag['length']-1;
                    break;

                case BTCODE_ALPHA_INVALID:
                    $tag['element']	= "invalid";
                    $append 		= substr($decodestring, 0, $tag['position']);
                    $newposition	= $tag['position'] + $tag['length']-1;
                    break;

                case BTCODE_UNKNOWN_INVALID:
                    $tag['element']	= "invalid";
                    $append 		= substr($decodestring, 0, $tag['position']);
                    $newposition	= $tag['position'];
                    break;
            }

            // Exclude code
            if (isset($tagsopen[BTCODE_EXCLUDE_TAG])) {
                if ($excludetmp == "") {
                    // starttag
                    $excludetmp = $decodestring;
                }

                if ($tag['element'] === BTCODE_EXCLUDE_TAG) {
                    // endtag - get everything between the starttag and
                    // the position of the endtag
                    $substr = substr($decodestring, $tag['position']);
                    $excludetmp = substr($excludetmp, 0, strpos($excludetmp, $substr));

                    $result .= $excludetmp;
                    $result .= self::_closeHTMLTag($tag, $tagsopen);
                } else {
                    // continue to search for tags
                    $decodestring = substr($decodestring,$newposition+2);
                    continue;
                }
            } else {
                $result .= $append;

                if (!isset($tagsopen[$tag['element']])) {
                    if ($spantags) {
                        $result .= self::_openHTMLTag($tag, $tagsopen);
                    } else {
                        if (substr($result, -1, 1) !== " ") {
                            $result .= "btcode_". $tag['element'] . " ";
                        } else {
                            $result .= "btcode_". $tag['element'];
                        }
                    }
                } else {
                    if ($spantags) {
                        $result .= self::_closeHTMLTag($tag, $tagsopen);
                    }
                }
            }
            $decodestring = substr($decodestring,$newposition+2);
        }

        $result .= $decodestring;

        // close all open tags (normally only the last one)
        foreach ($tagsopen as $opentag) {
            $result .= self::_closeHTMLTag($opentag, $tagsopen);
        }

        return $result;
    }

    /**
     * Identify Backtick-Tag
     * @access private
     * @param string $decodestring String with the tag in it
     * @param array $tag Tag-Element to identify
     * @return int Returns BTCODE_NUMERIC* or BTCODE_ALPHA* or BTCODE_UNKNOWN_INVALID
     */
    private static function _identifyTag($decodestring, &$tag)
    {
        if (is_numeric(substr($decodestring, $tag['position']+1, BTCODE_NUMERIC_LENGTH))) {
            // the codetag after the backtick is numeric
            $tag['length'] 	= BTCODE_NUMERIC_LENGTH;

               if (substr($decodestring, $tag['position']+1+BTCODE_NUMERIC_LENGTH, 1) == BTCODE_LAYER_IDENTIFIER) {
                   $tag['layer'] = BTCODE_LAYER_BACKGROUND;
               } else {
                   $tag['layer'] = BTCODE_LAYER_FOREGROUND;
               }

               return BTCODE_NUMERIC_VALID;
        } elseif (is_numeric(substr($decodestring, $tag['position']+1, 1))) {
            $tag['length'] = 1;
            for ($i=2; $i<=BTCODE_NUMERIC_LENGTH; $i++) {
                if (is_numeric(substr($decodestring, $tag['position']+1, $i))) {
                    $tag['length']++;
                }
            }

            // this is meant to be a numeric code, but the lenght doesn't match
            return BTCODE_NUMERIC_INVALID;
        } elseif (is_alpha(substr($decodestring, $tag['position']+1, BTCODE_ALPHA_LENGTH))) {
            // the codetag after the backtick is alpha
            $tag['length'] 	= BTCODE_ALPHA_LENGTH;

            return BTCODE_ALPHA_VALID;
        } elseif (is_alpha(substr($decodestring, $tag['position']+1, 1))) {
            $tag['length'] = 1;
            for ($i=2; $i<=BTCODE_ALPHA_LENGTH; $i++) {
                if (is_alpha(substr($decodestring, $tag['position']+1, $i))) {
                    $tag['length']++;
                }
            }

            // this is meant to be an alpha code, but the lenght doesn't match
            return BTCODE_ALPHA_INVALID;
        } else {
            return BTCODE_UNKNOWN_INVALID;
        }

    }

    /**
     * Resolves the correct opening HTML-Tag for the tag-element
     * @access private
     * @param array &$tag Tag-Element to use
     * @param array &$tagsopen Array of open Tags
     * @return int Returns translated HTML-Code with an opening Tag
     */
    private static function _openHTMLTag(&$tag, &$tagsopen)
    {
        $htmltag = "";

        switch ($tag['element']) {
            case "b": // special handling for bold
                // Set elementtype
                $tag['elementtype']			= "bold";

                if (isset($tagsopen[$tag['element']])) {
                    $htmltag .= self::_closeHTMLTag($tag, $tagsopen);
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "<strong>";
                break;

            case "c": // special handling for center
                // Set elementtype
                $tag['elementtype']			= "center";

                if (isset($tagsopen[$tag['element']])) {
                    $htmltag .= self::_closeHTMLTag($tag, $tagsopen);
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "<div class='btcode_c'>";
                break;

            case "g": //special handling for big
                // Set elementtype
                $tag['elementtype']			= "big";

                if (isset($tagsopen[$tag['element']])) {
                    $htmltag .= self::_closeHTMLTag($tag, $tagsopen);
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "<big>";
                break;

            case "i": // special handling for italic
                // Set elementtype
                $tag['elementtype']			= "italic";

                if (isset($tagsopen[$tag['element']])) {
                    $htmltag .= self::_closeHTMLTag($tag, $tagsopen);
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "<em>";
                break;

            case "n": // special handling for newline
                // Set elementtype
                $tag['elementtype']			= "newline";

                $htmltag 					.= "<br />";
                break;

            case "p": //special handling for sup
                // Set elementtype
                $tag['elementtype']			= "sup";

                if (isset($tagsopen[$tag['element']])) {
                    $htmltag .= self::_closeHTMLTag($tag, $tagsopen);
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "<sup>";
                break;

            case "s": //special handling for small
                // Set elementtype
                $tag['elementtype']			= "small";

                if (isset($tagsopen[$tag['element']])) {
                    $htmltag .= self::_closeHTMLTag($tag, $tagsopen);
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "<small>";
                break;

            case "u": //special handling for sub
                // Set elementtype
                $tag['elementtype']			= "sub";

                if (isset($tagsopen[$tag['element']])) {
                    $htmltag .= self::_closeHTMLTag($tag, $tagsopen);
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "<sub>";
                break;

            case "x": //special handling for nodecode
                // Set elementtype
                $tag['elementtype']			= "nodecode";

                if (isset($tagsopen[$tag['element']])) {
                    $htmltag .= self::_closeHTMLTag($tag, $tagsopen);
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "";
                break;

            default: // color codes
                // Set elementtype
                if (strstr($tag['element'], BTCODE_LAYER_BACKGROUND_SUFFIX) !== false) {
                    $tag['elementtype']			= "bgcolor";
                } else {
                    $tag['elementtype']			= "color";
                }

                // test if any color-code is opened
                foreach ($tagsopen as $opentag) {
                    if ($opentag['elementtype'] == $tag['elementtype']) {
                        //echo "closing ". $opentag['element'] ."<br>";
                        $htmltag .= self::_closeHTMLTag($opentag, $tagsopen);
                    }
                }

                $tagsopen[$tag['element']] 	= $tag;
                $htmltag 					.= "<span class='btcode_". $tag['element'] ."'>";
                break;
        }

        return $htmltag;
    }

    /**
     * Resolves the correct closing HTML-Tag for the tag-element
     * @access private
     * @param array &$tag Tag-Element to use
     * @param array &$tagsopen Array of open Tags
     * @return int Returns translated HTML-Code with an closing Tags
     */
    private static function _closeHTMLTag($tag, &$tagsopen)
    {
        switch ($tag['element']) {
            case "b": // special handling for bold
                unset ($tagsopen[$tag['element']]);
                $htmltag = "</strong>";
                break;

            case "c": // special handling for center
                unset ($tagsopen[$tag['element']]);
                $htmltag = "</div>";
                break;

            case "g": //special handling for big
                unset ($tagsopen[$tag['element']]);
                $htmltag = "</big>";
                break;

            case "i": //special handling for italic
                unset ($tagsopen[$tag['element']]);
                $htmltag = "</em>";
                break;

            case "n": // special handling for newline
                $htmltag = "";
                break;

            case "p": //special handling for sup
                unset ($tagsopen[$tag['element']]);
                $htmltag = "</sup>";
                break;

             case "s": //special handling for small
                unset ($tagsopen[$tag['element']]);
                $htmltag = "</small>";
                break;

            case "u": //special handling for sub
                unset ($tagsopen[$tag['element']]);
                $htmltag = "</sub>";
                break;

            case "x": //special handling for nodecode
                unset ($tagsopen[$tag['element']]);
                $htmltag = "";
                break;

            default: // color codes
                unset ($tagsopen[$tag['element']]);
                $htmltag = "</span>";
                break;
        }

        return $htmltag;
    }

}
?>
