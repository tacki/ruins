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
use Ruins\Common\Manager\RequestManager;

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
    * Holds the Base of the current URL
    * @var string
    */
    private $base;

    /**
     * Holds an array of
     * @var array
     */
    private $query = array();

    /**
     * Constructor - Loads the default values and initializes the attributes
     * @param Request $request
     */
    function __construct(Request $request)
    {
        // Initialize
        $this->base = $request->getRouteAsString();
        $this->query = $request->getQueryAsArray();
    }

    /**
     * If the Class is handled as a string, return current URL
     * @return string
     */
    function __toString()
    {
        $path = $this->getBase();

        if (count($this->query)) {
            $path .= "?" . http_build_query($this->query);
        }

        return $path;
    }

    /**
     * Get Base Path
     * Enter description here ...
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Get Full Path inclusive all Parameters
     * @return string
     */
    public function getFull()
    {
        $path = RequestManager::getWebBasePath() . "/" . $this->getBase();

        if (count($this->query)) {
            $path .= "?" . http_build_query($this->query);
        }

        return $path;
    }

    /**
     * Get the value of a given GET-Parameter
     * @param string $parameter Name of the GET-Parameter
     * @return string Value of the GET-Parameter
     */
    public function getParameter($parameter)
    {
        if (isset($this->query[$parameter])) {
            return $this->query[$parameter];
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
            if (isset($this->query[$parameter])) {
                // Remove the Parameter
                unset ($this->query[$parameter]);
            }
        } else {
            // Remove all parameters
            $this->query = array();;
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
        $this->query[$parameter] = $value;

        return $this;
    }
}
?>
