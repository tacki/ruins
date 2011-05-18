<?php
/**
 * URL Handling Class
 *
 * Class to handle URLs
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Sebastian Meyer
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: url.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Basic HTML Class
 *
 * Baseclass for all HTML-based classes
 * @package Ruins
 */
class URL
{
    /**
     * @var string
     */
    private $_url;

    /**
     * Holds the Base of the current URL
     * @var string
     */
    public $base;

    /**
     * Holds the shortvar of the Base
     * @var string
     */
    public $short;

    /**
     * Constructor - Loads the default values and initializes the attributes
     * @param mixed $outputclass Class which handles the output
     */
    function __construct($initURL)
    {
        // Initialize
        $this->_url = html_entity_decode($initURL);
        $this->base = array_shift(explode("&", $this->_url));
        $this->short = array_pop(explode("page=", $this->base));
    }

    /**
     * If the Class is handled as a string, return current URL
     * @return string
     */
    function __toString()
    {
        return $this->_url;
    }

    /**
     * Get the value of a given GET-Parameter
     * @param string $parameter Name of the GET-Parameter
     * @return string Value of the GET-Parameter
     */
    public function getParameter($parameter)
    {
        parse_str($this->_url, $parameters);

        if (isset($parameters[$parameter])) {
            return $parameters[$parameter];
        } else {
            return false;
        }
    }

    /**
     * Unset a given Parameter from the URL
     * @param string $parameter Name of the GET-Parameter
     */
    public function unsetParameter($parameter)
    {
        $oldvalue = $this->getParameter($parameter);
        if ($oldvalue !== false) {
            // Remove the Parameter
            $this->_url = str_replace("&".$parameter."=".$oldvalue, "", $this->_url);
        }
    }

    /**
     * Change or add a Parameter to the URL
     * @param string $parameter Name of the GET-Parameter
     * @param string $value Value of the GET-Parameter
     */
    public function setParameter($parameter, $value)
    {
        $oldvalue = $this->getParameter($parameter);
        if ($oldvalue !== false) {
            // Change the Parameter
            $this->_url = str_replace($parameter."=".$oldvalue, $parameter."=".$value, $this->_url);
        } else {
            // Add the Parameter
            $this->_url = $this->_url."&".$parameter."=".$value;
        }
    }
}
?>
