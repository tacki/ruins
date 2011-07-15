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
class Request
{
    protected $route = array();
    protected $routeString;

    protected $query = array();
    protected $queryString;

    protected $completeRoute;

    public function __construct($completeRoute)
    {
        $routeParts = explode('?', $completeRoute, 2);

        $this->routeString = $routeParts[0];
        $this->queryString = $routeParts[1];
        $this->completeRoute = $completeRoute;

        $this->setRoute($this->generateRouteArray());
        $this->setQuery($this->generateQueryArray());
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param array $route
     */
    public function setRoute(array $route)
    {
        $this->route = $route;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getRouteString()
    {
        return $this->routeString;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @return string
     */
    public function getCompleteRoute()
    {
        return $this->completeRoute;
    }

    /**
     * Get Route Caller
     * @return string
     */
    public function getRouteCaller()
    {
        if (!($result = key($this->route))) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get Route Parameters
     * @return string
     */
    public function getRouteParameters()
    {
        $result = implode("/", current($this->route));

        return $result;
    }

    /**
     * Generate Route Array from Route String
     * Example Result:
     * array ( 'page' => array ( 'common', 'login' ) )
     * @return array
     */
    private function generateRouteArray()
    {
        $routeArray = array();
        $routeString = $this->routeString;

        // Strip first slash
        if (substr($routeString, 0, 1) == "/") {
            $routeString = substr($routeString, 1);
        }

        if (strlen($routeString) === 0) {
            return $routeArray;
        }

        $explodedRoute = explode("/", $routeString);

        // First Element = Route Caller
        $routeCaller = array_shift($explodedRoute);

        // Rest of the RouteString = Route
        $routeArray = array( $routeCaller => $explodedRoute );

        return $routeArray;
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