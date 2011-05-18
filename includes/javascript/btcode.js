function btCode()
{
        /**
         * Class Defines
         */
        var BTCODE_NUMERIC_LENGTH 			= 2;
        var BTCODE_ALPHA_LENGTH 			= 1;

        var BTCODE_NUMERIC_VALID 			= 10;
        var BTCODE_NUMERIC_INVALID 			= 11;
        var BTCODE_ALPHA_VALID 				= 20;
        var BTCODE_ALPHA_INVALID 			= 21;
        var BTCODE_UNKNOWN_INVALID 			= 99;

        var BTCODE_LAYER_IDENTIFIER 		= "~";
        var BTCODE_LAYER_FOREGROUND 		= 10;
        var BTCODE_LAYER_BACKGROUND 		= 20;
        var BTCODE_LAYER_BACKGROUND_SUFFIX 	= "_bg";

        var BTCODE_EXCLUDE_TAG				= "x";

        /**
         * Convert Backtick-Tags into <span>-Elements
         * @param string $decodestring String to convert
         * @return string Returns HTML-Code with converted Backtick-Tags
         */
        this.decode = function (decodestring)
        {
            return this._decodebtCode(decodestring, true);
        };

        /**
         * Convert Backtick-Tags into CSS-Classnames
         * @param string $decodestring String to convert
         * @return string Returns CSS-Classnames
         */
        this.decoderaw = function (decodestring)
        {
            return this._decodebtCode(decodestring, false);
        };

        /**
         * Protect a given string from being decoded
         * @param string $nodecodestring String to protect
         * @return string protected String
         */
        this.exclude = function (nodecodestring)
        {
            return "`" + BTCODE_EXCLUDE_TAG + $nodecodestring + "`" + BTCODE_EXCLUDE_TAG;
        };

        /**
         * Purge btCode-tags
         * @param string $decodestring String to purge
         * @return string String without btCode-tags
         */
        this.purgeTags = function (decodestring)
        {
            var digits = "";
            for (var i=0; i<BTCODE_NUMERIC_LENGTH; i++) {
                digits = digits+"[0-9]";
            }

            var alphas = "";
            for (var i=0; i<BTCODE_ALPHA_LENGTH; i++) {
                alphas += "[a-zA-Z]";
            }

            // remove numeric tags (colors)
            var replace = new RegExp("`"+digits, "g");
            decodestring = decodestring.replace(replace, "");

            // remove alpha tags (bold, center, big, ...)
            var replace = new RegExp("`"+alphas, "g");
            decodestring = decodestring.replace(replace, "");

            return decodestring;
        };

        /**
         * Convert Backtick-Tags
         * @access private
         * @param string $decodestring String to convert
         * @param bool $spantags Include span-tags for colors
         * @return string Returns HTML-Code or simply the css-classes with converted Backtick-Tags
         */
        this._decodebtCode = function (decodestring, spantags)
        {
            var tag 		= new Object();
            var tagsopen 	= new Object();
            var result 		= "";
            var excludetmp	= "";

            while (!((tag['position'] = strpos(decodestring, "`")) === false)) {

                switch (this._identifyTag(decodestring, tag)) {
                    case BTCODE_NUMERIC_VALID:
                        if (tag['layer'] == BTCODE_LAYER_FOREGROUND) {
                            tag['element'] 	= decodestring.substr(tag['position']+1, BTCODE_NUMERIC_LENGTH);
                            var append 		= decodestring.substr(0, tag['position']);
                            var newposition	= tag['position'] + BTCODE_NUMERIC_LENGTH-1;
                        } else {
                            tag['element'] 	= decodestring.substr(tag['position']+1, BTCODE_NUMERIC_LENGTH) + BTCODE_LAYER_BACKGROUND_SUFFIX;
                            var append 		= decodestring.substr(0, tag['position']);
                            var newposition	= tag['position'] + BTCODE_NUMERIC_LENGTH;
                        }
                        break;

                    case BTCODE_ALPHA_VALID:
                        tag['element']	= decodestring.substr(tag['position']+1, BTCODE_ALPHA_LENGTH);
                        var append 		= decodestring.substr(0, tag['position']);
                        var newposition	= tag['position'];
                        break;

                    case BTCODE_NUMERIC_INVALID:
                        tag['element']	= "invalid";
                        var append		= decodestring.substr(0, tag['position']);
                        var newposition	= tag['position'] + tag['length']-1;
                        break;

                    case BTCODE_ALPHA_INVALID:
                        tag['element']	= "invalid";
                        var append 		= decodestring.substr(0, tag['position']);
                        var newposition	= tag['position'] + tag['length']-1;
                        break;

                    case BTCODE_UNKNOWN_INVALID:
                        tag['element']	= "invalid";
                        var append 		= decodestring.substr(0, tag['position']);
                        var newposition	= tag['position'];
                        break;
                }

                // Exclude code
                if (isset(tagsopen[BTCODE_EXCLUDE_TAG])) {
                    if (excludetmp == "") {
                        // starttag
                        excludetmp = decodestring;
                    }

                    if (tag['element'] === BTCODE_EXCLUDE_TAG) {
                        // endtag - get everything between the starttag and
                        // the position of the endtag
                        substr = decodestring.substr(tag['position']);
                        excludetmp = excludetmp.substr(0, strpos(excludetmp, substr));

                        result = result + excludetmp;
                        result = result + this._closeHTMLTag(tag, tagsopen);
                    } else {
                        // continue to search for tags
                        decodestring = decodestring.substr(newposition+2);
                        continue;
                    }
                } else {
                    result = result+append;

                    if (!isset(tagsopen[tag['element']])) {
                        if (spantags) {
                            result = result+this._openHTMLTag(tag, tagsopen);
                        } else {
                            if (result.substr(-1, 1) !== " ") {
                                $result = result + "btcode_" + tag['element'] + " ";
                            } else {
                                $result = result + "btcode_" + tag['element'];
                            }
                        }
                    } else {
                        if (spantags) {
                            result = result + this._closeHTMLTag(tag, tagsopen);
                        }
                    }
                }
                decodestring = decodestring.substr(newposition+2);
            }

            result = result + decodestring;

            // close all open tags (normally only the last one)
            for (var key in tagsopen) {
                var opentag = tagsopen[key];

                result = result + this._closeHTMLTag(opentag,tagsopen);
            }

            return result;


        };

        /**
         * Identify Backtick-Tag
         * @access private
         * @param string $decodestring String with the tag in it
         * @param array $tag Tag-Element to identify
         * @return int Returns BTCODE_NUMERIC* or BTCODE_ALPHA* or BTCODE_UNKNOWN_INVALID
         */
        this._identifyTag = function (decodestring, tag)
        {
            if (is_numeric(decodestring.substr(tag['position']+1, BTCODE_NUMERIC_LENGTH))) {
                // the codetag after the backtick is numeric
                tag['length'] = BTCODE_NUMERIC_LENGTH;

                   if (decodestring.substr(tag['position']+1+BTCODE_NUMERIC_LENGTH, 1) == BTCODE_LAYER_IDENTIFIER) {
                       tag['layer'] = BTCODE_LAYER_BACKGROUND;
                   } else {
                       tag['layer'] = BTCODE_LAYER_FOREGROUND;
                   }

                   return BTCODE_NUMERIC_VALID;
            } else if (is_numeric(decodestring.substr(tag['position']+1, 1))) {
                tag['length'] = 1;

                for (var i=2; i<=BTCODE_NUMERIC_LENGTH; i++) {
                    if (is_numeric(decodestring.substr(tag['position']+1, i))) {
                        tag['length']++;
                    }
                }

                // this is meant to be a numeric code, but the lenght doesn't match
                return BTCODE_NUMERIC_INVALID;
            } else if (typeof(decodestring.substr(tag['position']+1, BTCODE_ALPHA_LENGTH)) == 'string') {
                // the codetag after the backtick is alpha
                tag['length'] = BTCODE_ALPHA_LENGTH;

                return BTCODE_ALPHA_VALID;
            } else if (typeof(decodestring.substr(tag['position']+1, 1)) == 'string') {
                tag['length'] = 1;

                for (var i=2; i<=BTCODE_ALPHA_LENGTH; $i++) {
                    if (typeof(decodestring.substr($tag['position']+1, $i)) == 'string') {
                        $tag['length']++;
                    }
                }

                // this is meant to be an alpha code, but the lenght doesn't match
                return BTCODE_ALPHA_INVALID;
            } else {
                return BTCODE_UNKNOWN_INVALID;
            }

        };

        /**
         * Resolves the correct opening HTML-Tag for the tag-element
         * @access private
         * @param array &$tag Tag-Element to use
         * @param array &$tagsopen Array of open Tags
         * @return int Returns translated HTML-Code with an opening Tag
         */
        this._openHTMLTag = function (tag, tagsopen)
        {
            var htmltag = "";

            switch (tag['element']) {
                case "b": // special handling for bold
                    // Set elementtype
                    tag['elementtype']			= "bold";

                    if (isset(tagsopen[tag['element']])) {
                        htmltag = htmltag + this._closeHTMLTag(tag, tagsopen);
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "<strong>";
                    break;

                case "c": // special handling for center
                    // Set elementtype
                    tag['elementtype']			= "center";

                    if (isset(tagsopen[tag['element']])) {
                        htmltag = htmltag + this._closeHTMLTag(tag, tagsopen);
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "<div class='btcode_c'>";
                    break;

                case "g": //special handling for big
                    // Set elementtype
                    tag['elementtype']			= "big";

                    if (isset(tagsopen[tag['element']])) {
                        htmltag = htmltag + this._closeHTMLTag(tag, tagsopen);
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "<big>";
                    break;

                case "i": // special handling for italic
                    // Set elementtype
                    tag['elementtype']			= "italic";

                    if (isset(tagsopen[tag['element']])) {
                        htmltag = htmltag + this._closeHTMLTag($tag, tagsopen);
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "<em>";
                    break;

                case "n": // special handling for newline
                    // Set elementtype
                    tag['elementtype']			= "newline";

                    htmltag 					= htmltag + "<br />";
                    break;

                case "p": //special handling for sup
                    // Set elementtype
                    tag['elementtype']			= "sup";

                    if (isset(tagsopen[tag['element']])) {
                        htmltag = htmltag + this.__closeHTMLTag(tag, tagsopen);
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "<sup>";
                    break;

                case "s": //special handling for small
                    // Set elementtype
                    tag['elementtype']			= "small";

                    if (isset(tagsopen[tag['element']])) {
                        htmltag = htmltag + this.__closeHTMLTag(tag, tagsopen);
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "<small>";
                    break;

                case "u": //special handling for sub
                    // Set elementtype
                    tag['elementtype']			= "sub";

                    if (isset(tagsopen[tag['element']])) {
                        htmltag = htmltag + this.__closeHTMLTag(tag, tagsopen);
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "<sub>";
                    break;

                case "x": //special handling for nodecode
                    // Set elementtype
                    $tag['elementtype']			= "nodecode";

                    if (isset(tagsopen[tag['element']])) {
                        htmltag = htmltag + this._closeHTMLTag(tag, tagsopen);
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "";
                    break;

                default: // color codes
                    // Set elementtype
                    if (strstr(tag['element'], BTCODE_LAYER_BACKGROUND_SUFFIX) !== false) {
                        tag['elementtype']		= "bgcolor";
                    } else {
                        tag['elementtype']		= "color";
                    }

                    // test if any color-code is opened
                    for (var key in tagsopen) {
                        var opentag = tagsopen[key];

                        if (opentag['elementtype'] == tag['elementtype']) {
                            htmltag = htmltag + this._closeHTMLTag(opentag, tagsopen);
                        }
                    }

                    tagsopen[tag['element']] 	= tag;
                    htmltag 					= htmltag + "<span class='btcode_" + tag['element'] + "'>";
                    break;
            }

            return htmltag;
        };

        /**
         * Resolves the correct closing HTML-Tag for the tag-element
         * @access private
         * @param array &$tag Tag-Element to use
         * @param array &$tagsopen Array of open Tags
         * @return int Returns translated HTML-Code with an closing Tags
         */
        this._closeHTMLTag = function (tag, tagsopen)
        {
            switch (tag['element']) {
                case "b": // special handling for bold
                    delete (tagsopen[tag['element']]);
                    var htmltag = "</strong>";
                    break;

                case "c": // special handling for center
                    delete (tagsopen[tag['element']]);
                    var htmltag = "</div>";
                    break;

                case "g": //special handling for big
                    delete (tagsopen[tag['element']]);
                    var htmltag = "</big>";
                    break;

                case "i": //special handling for italic
                    delete (tagsopen[tag['element']]);
                    var htmltag = "</em>";
                    break;

                case "n": // special handling for newline
                    var htmltag = "";
                    break;

                case "p": //special handling for sup
                    delete (tagsopen[tag['element']]);
                    var htmltag = "</sup>";
                    break;

                 case "s": //special handling for small
                     delete (tagsopen[tag['element']]);
                    var htmltag = "</small>";
                    break;

                case "u": //special handling for sub
                    delete (tagsopen[tag['element']]);
                    var htmltag = "</sub>";
                    break;

                case "x": //special handling for nodecode
                    delete (tagsopen[tag['element']]);
                    var htmltag = "";
                    break;

                default: // color codes
                    delete (tagsopen[tag['element']]);
                    var htmltag = "</span>";
                    break;
            }

            return htmltag;
        };
}
