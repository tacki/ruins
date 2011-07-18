<?php
/**
 * Route Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Controller;
use Ruins\Main\Manager\SystemManager;

/**
 * Route Class
 * @package Ruins
 */
class Route
{
    /**
     * @var string
     */
    protected $routeString;

    /**
     * @var string
     */
    protected $caller;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var array
     */
    protected $queryExtra = array();

    /**
     * Create and Initialize
     * @param string $route
     */
    public function __construct($route)
    {
        $this->routeString = (string)$route;

        // Inititalize Route and collect Information
        // about the Route Caller and the parameters
        $this->initRoute();

        // Try to guess corresponding Route Filename
        $this->filename = $this->getRouteFile($route);
    }

    /**
     * Get Caller Name
     * @return string
     */
    public function getCaller()
    {
        return $this->caller;
    }

    /**
     * Get Call-Parameters
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get Controller Filename
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get Query Extra
     * @return array
     */
    public function getQueryExtra()
    {
        return $this->queryExtra;
    }

    /**
     * Get Classname from Filename
     * @return boolean|string
     */
    public function getClassname()
    {
        if (!$this->filename) {
            return false;
        }

        // Strip Source Base
        $classname = substr($this->filename, strlen(DIR_BASE."src"));

        // Replace / with \
        $classname = str_replace("/", "\\", $classname);

        // Strip .php Ending
        $classname = substr($classname, 0, -4);

        return $classname;
    }

    /**
     * Initialize Route
     */
    private function initRoute()
    {
        // Strip existing slash in front
        if (substr($this->routeString, 0, 1) == "/") {
            $this->routeString = substr($this->routeString, 1);
        }

        if (strlen($this->routeString) === 0) {
            $this->caller = false;
            $this->parameters = array();
        } else {
            $explodedRoute = explode("/", $this->routeString);

            // First Element = Route Caller
            $this->caller = array_shift($explodedRoute);

            // Rest of the Route = Route Parameters
            $this->parameters = $explodedRoute;
        }
    }

    /**
     * Find the matching Controller for this Route
     * @return string
     */
    private function getRouteFile()
    {
        // FilePart initialization
        $filepart = "Pages/".$this->caller;

        // Find Routefile
        while (count($this->parameters)) {
            // Create Filename and check if it exists in our tree
            $filepart .= "/".array_shift($this->parameters);

            if ($result = SystemManager::findFileInTree($filepart.".php")) {
                // Add the Rest of the Parameters to queryExtra
                // beginning with key 'op', then 'op1', 'op2'...
                for ($j=0; $j<count($this->parameters); $j++) {
                    if ($j == 0) {
                        $this->queryExtra['op'] = $this->parameters[$j];
                    } else {
                        $this->queryExtra['op'.$j] = $this->parameters;
                    }
                }
                return $result;
            }
        }

        return false;
    }
}