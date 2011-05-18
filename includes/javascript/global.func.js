/**
 * Global Java Functions
 *
 * Functions worldwide useable:D
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

var checkflag = false;

function submitform(formname,action) {
    document.getElementsByName(formname)[0].action.value = action ;
    document.getElementsByName(formname)[0].submit() ;
}

function checkall(formname) {
    formlength = document.getElementsByName(formname)[0].elements.length;
    if (checkflag == false){
        for (i = 0; i < formlength; i++){
            document.getElementsByName(formname)[0].elements[i].checked = true;
        }
        checkflag = true;
    } else {
        for (i = 0; i < formlength; i++) {
            document.getElementsByName(formname)[0].elements[i].checked = false;
        }
        checkflag = false;
    }
}

//Returns the entries of arr1 that have values which are not present in any of the others arguments.
//
// version: 910.912
// discuss at: http://phpjs.org/functions/array_diff
// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   improved by: Sanjoy Roy
// +    revised by: Brett Zamir (http://brett-zamir.me)
// *     example 1: array_diff(['Kevin', 'van', 'Zonneveld'], ['van', 'Zonneveld']);
// *     returns 1: {0:'Kevin'}
function array_diff ()
{
    var arr1 = arguments[0], retArr = {};
    var k1 = '', i = 1, k = '', arr = {};

    arr1keys:
        for (k1 in arr1) {
            for (i = 1; i < arguments.length; i++) {
                arr = arguments[i];
                for (k in arr) {
                    if (arr[k] === arr1[k1]) {
                        // If it reaches here, it was found in at least one array, so try next value
                        continue arr1keys;
                    }
                }
            retArr[k1] = arr1[k1];
            }
        }

    return retArr;
}

// Returns true if value is a number or a numeric string
//
// version: 911.718
// discuss at: http://phpjs.org/functions/is_numeric
// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   improved by: David
// +   improved by: taith
// +   bugfixed by: Tim de Koning
// +   bugfixed by: WebDevHobo (http://webdevhobo.blogspot.com/)
// +   bugfixed by: Brett Zamir (http://brett-zamir.me)
// *     example 1: is_numeric(186.31);
// *     returns 1: true
// *     example 2: is_numeric('Kevin van Zonneveld');
// *     returns 2: false
// *     example 3: is_numeric('+186.31e2');
// *     returns 3: true
// *     example 4: is_numeric('');
// *     returns 4: false
// *     example 4: is_numeric([]);
// *     returns 4: false
function is_numeric (mixed_var)
{
    return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}

// !No description available for isset. @php.js developers: Please update the function summary text file.
//
// version: 909.322
// discuss at: http://phpjs.org/functions/isset
// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   improved by: FremyCompany
// +   improved by: Onno Marsman
// *     example 1: isset( undefined, true);
// *     returns 1: false
// *     example 2: isset( 'Kevin van Zonneveld' );
// *     returns 2: true
function isset ()
{
    var a=arguments, l=a.length, i=0;

    if (l===0) {
        throw new Error('Empty isset');
    }

    while (i!==l) {
        if (typeof(a[i])=='undefined' || a[i]===null) {
            return false;
        } else {
            i++;
        }
    }
    return true;
}

// Finds first occurrence of a string within another
//
// version: 909.322
// discuss at: http://phpjs.org/functions/strstr
// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   bugfixed by: Onno Marsman
// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// *     example 1: strstr('Kevin van Zonneveld', 'van');
// *     returns 1: 'van Zonneveld'
// *     example 2: strstr('Kevin van Zonneveld', 'van', true);
// *     returns 2: 'Kevin '
// *     example 3: strstr('name@example.com', '@');
// *     returns 3: '@example.com'
// *     example 4: strstr('name@example.com', '@', true);
// *     returns 4: 'name'
function strstr (haystack, needle, bool)
{
    var pos = 0;

    haystack += '';
    pos = haystack.indexOf( needle );
    if (pos == -1) {
        return false;
    } else{
        if (bool){
            return haystack.substr( 0, pos );
        } else{
            return haystack.slice( pos );
        }
    }
}

// Finds position of first occurrence of a string within another
//
// version: 909.322
// discuss at: http://phpjs.org/functions/strpos
// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   improved by: Onno Marsman
// +   bugfixed by: Daniel Esteban
// *     example 1: strpos('Kevin van Zonneveld', 'e', 5);
// *     returns 1: 14
function strpos (haystack, needle, offset)
{
    var i = (haystack+'').indexOf(needle, (offset ? offset : 0));
    return i === -1 ? false : i;
}


/**
*
* URL encode / decode
* http://www.webtoolkit.info/
*
**/
var Url = {

    // public method for url encoding
    encode : function (string) {
        return escape(this._utf8_encode(string));
    },

    // public method for url decoding
    decode : function (string) {
        return this._utf8_decode(unescape(string));
    },

    // private method for UTF-8 encoding
    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

};

