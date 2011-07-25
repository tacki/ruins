<?php
use Ruins\Common\Manager\RequestManager;
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.basepath.php
 * Type:     function
 * Name:     basepath
 * Purpose:  returns the web-basepath
 * -------------------------------------------------------------
 */
function smarty_function_basepath($params, $template)
{
    // Find Front Controller
    $frontCntrl = $_SERVER['SCRIPT_NAME'];
    // Call-Parameter
    $call = $params['call'];

    // Remove Front-Controller
    if (strpos($_SERVER['REQUEST_URI'], $frontCntrl) === false) {
        // Front Controller is removed by mod_rewrite
        $result = pathinfo($frontCntrl, PATHINFO_DIRNAME);
    } else {
        $result = $frontCntrl;
    }

    if ($call) {
        // strip all leading slashes
        while (substr($call,0,1) == "/") {
            $call = substr($call,1);
        }

        $result .= "/" . $call;
    }

    return $result;
}