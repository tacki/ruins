<?php
/**
 * URL Handling Class
 *
 * Class to handle URLs
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Sebastian Meyer
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Controller;
use Ruins\Common\Controller\Request;

/**
 * Basic HTML Class
 *
 * Baseclass for all HTML-based classes
 * @package Ruins
 */
class Url
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
     * Constructor - Loads the default values and initializes the attributes
     * @param Request $request
     */
    function __construct(Request $request)
    {
        // Initialize
        $this->_url = html_entity_decode($request->getCompleteRequest());
        $this->base = $request->getRoute()->getRouteBase();
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
     * @return URL
     */
    public function unsetParameter($parameter=false)
    {
        if ($parameter) {
            $oldvalue = $this->getParameter($parameter);
            if ($oldvalue !== false) {
                // Remove the Parameter
                $this->_url = str_replace("?".$parameter."=".$oldvalue, "", $this->_url);
                $this->_url = str_replace("&".$parameter."=".$oldvalue, "", $this->_url);
            }
        } else {
            $this->_url = $this->base;
        }

        return $this;
    }

    /**
     * Change or add a Parameter to the URL
     * @param string $parameter Name of the GET-Parameter
     * @param string $value Value of the GET-Parameter
     * @return URL
     */
    public function setParameter($parameter, $value)
    {
        $oldvalue = $this->getParameter($parameter);
        if ($oldvalue !== false) {
            // Change the Parameter
            $this->_url = str_replace($parameter."=".$oldvalue, $parameter."=".$value, $this->_url);
        } else {
            // Add the Parameter
            if (strstr($this->_url, "?") === false) {
                $this->_url = $this->_url."?".$parameter."=".$value;
            } else {
                $this->_url = $this->_url."&".$parameter."=".$value;
            }
        }

        return $this;
    }
}
?>
