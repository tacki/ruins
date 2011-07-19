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
    /**
     * Retrieve Route Request String
     * @var string $requestURI
     * @return Request
     */
    public static function createRequest($routeRequest=false)
    {
        if (!$routeRequest) {
            $routeRequest = self::getCurrentShortUrl();
        }

        // strip leading slashes
        while (substr($routeRequest,0,1) == "/") {
            $routeRequest = substr($routeRequest,1);
        }

        return new Request($routeRequest);
    }

    /**
     * Retrieve WebBasePath (example: /ruins/web or /ruins/web/app.php)
     * @return string
     */
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
     * Retrieve Url without WebBasePath
     * @return string
     */
    public static function getCurrentShortUrl()
    {
        $requestURI = $_SERVER['REQUEST_URI'];

        $routeRequest = substr($requestURI, strlen(self::getWebBasePath()));

        // strip leading slashes
        while (substr($routeRequest,0,1) == "/") {
            $routeRequest = substr($routeRequest,1);
        }

        return $routeRequest;
    }
}