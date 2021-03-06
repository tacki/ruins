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
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Common\Exceptions\Error;
use Ruins\Main\Manager\SystemManager;

/**
 * Route Class
 * @package Ruins
 */
class Route
{
    const STATUS_VALID   = "1";
    const STATUS_INVALID = "2";

    /**
     * @var string
     */
    protected $routeString;

    /**
     * @var string
     */
    protected $caller;

    /**
     * @var string
     */
    protected $routeBase;

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
     * @var int
     */
    protected $status = self::STATUS_INVALID;

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
        if (strlen($route)) {
            $this->filename = $this->getRouteFile($route);
            if ($this->filename) {
                $this->status = self::STATUS_VALID;
            }
        }
    }

    /**
     * Get Caller Name
     * @return string
     */
    public function getCallerName()
    {
        return $this->caller;
    }

    /**
     * Get Caller Output Object
     * @return OutputObjectInterface
     */
    public function getCallerObject()
    {
        $classname = 'Ruins\\Common\\Controller\\OutputObjects\\'.$this->caller;

        return new $classname;
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
     * Get Route Base without ExtraQuery
     * @return string
     */
    public function getRouteBase()
    {
        return $this->routeBase;
    }

    /**
     * Get Status of this Route
     * @return bool
     */
    public function isValid()
    {
        if ($this->status === self::STATUS_VALID) {
            return true;
        } else {
            return false;
        }
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
     * @throws Error
     * @return string
     */
    private function getRouteFile()
    {
        // FilePart initialization
        $filepart = "Pages/".$this->caller;

        // Find Routefile
        while (count($this->parameters)) {
            // Create Filename and check if it exists in our tree
            $filepart .= "/".ucfirst(array_shift($this->parameters));

            if (substr($filepart, -strlen($this->caller)) !== $this->caller) {
                $temppart = $this->caller;
            } else {
                $temppart = "";
            }

            if ($result = SystemManager::findFileInTree($filepart.$temppart.".php")) {
                // remember routeBase (remove Pages/)
                $this->routeBase = substr($filepart, strlen("Pages/"));

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
        //throw new Error("Cannot find RouteFile for Route-Request '$this->routeString'");
    }
}