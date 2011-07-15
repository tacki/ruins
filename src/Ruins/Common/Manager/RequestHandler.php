<?php
/**
 * Request Handler Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Manager;
use Ruins\Common\Controller\Request;

/**
 * Request Handler Class
 * @package Ruins
 */
class RequestHandler
{
    public static function getWebBasePath()
    {
        $frontCntrl = $_SERVER['SCRIPT_NAME'];

        // Remove Front-Controller
        if (strpos($_SERVER['REQUEST_URI'], $frontCntrl) === false) {
            // Front Controller is removed by mod_rewrite
            $result = pathinfo($frontCntrl, PATHINFO_DIRNAME);
        } else {
            $result = $frontCntrl;
        }

        return $result;
    }

    /**
     * Retrieve Route Request String
     * @var string $requestURI
     * @return Request
     */
    public static function getRequest($routeRequest=false)
    {
        if (!$routeRequest) {
            $requestURI = $_SERVER['REQUEST_URI'];

            $routeRequest = substr($requestURI, strlen(self::getWebBasePath()));
        }

        if ($routeRequest === false) {
            // empty or invalid request_uri = slash
            $routeRequest = "/";
        } else if (substr($routeRequest,0,1) !== "/") {
            // add a missing leading slash
            $routeRequest = "/".$routeRequest;
        }

        return self::createRequestObject($routeRequest);
    }

    /**
     * Create Request Object
     * @param string $routeRequest
     * @return Request
     */
    private static function createRequestObject($routeRequest)
    {
        return new Request($routeRequest);
    }
}