<?php
/**
 * Request Class
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

/**
 * Request Class
 * @package Ruins
 */
use Ruins\Main\Manager\SystemManager;
use Ruins\Common\Controller\Route;

class Request
{
    /**
     * @var Route
     */
    protected $route;

    /**
    * @var string
    */
    protected $routeString;

    /**
     * @var array
     */
    protected $query = array();

    /**
     * @var string
     */
    protected $queryString;

    /**
     * Create and Initialize
     * @param string $completeRequest
     */
    public function __construct($completeRoute)
    {
        $requestParts = explode('?', $completeRoute, 2);

        // The Route as a String
        $this->routeString = $requestParts[0];

        // The Routing-Part is handled by our
        // Route-Object
        $this->route = new Route($requestParts[0]);

        // The extra Query-Part
        $this->queryString = $requestParts[1];

        // Generate Route and Query Arrays
        $this->query = $this->generateQueryArray();
    }

    /**
     * Get Route Object
     * @return Ruins\Common\Controller\Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get Request-Query as Array
     * @return array
     */
    public function getQueryAsArray()
    {
        return $this->query;
    }

    /**
     * Get Request-Query as String
     * @return string
     */
    public function getQueryAsString()
    {
        return $this->queryString;
    }

    /**
     * Get Route as String
     * @return string
     */
    public function getRouteAsString()
    {
        return $this->routeString;
    }

    /**
     * Generate Query Array from Query String
     * Example Result:
     * array ( 'op' => 'fag', 'action' => 'die'  )
     * @param string $queryString
     * @return array
     */
    private function generateQueryArray()
    {
        $queryArray = array();
        $queryString = $this->queryString;

        if (strlen($queryString) === 0) {
            return $queryArray;
        }

        $explodedQuery = explode("&", $queryString);

        foreach ($explodedQuery as $query) {
            $queryParams = explode("=", $query);

            $queryArray[$queryParams[0]] = $queryParams[1];
        }

        return $queryArray;
    }
}