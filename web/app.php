<?php
/**
 * Primary Index
 *
 * Index and Caller-Page of Ruins
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Ruins\Common\Controller\SessionStore;
use Ruins\Main\Controller\Nav;
use Ruins\Main\Manager\SystemManager;
use Ruins\Main\Manager\ModuleManager;
use Ruins\Main\Controller\Link;
use Ruins\Main\Controller\Page;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Manager\RequestHandler;

/**
 * Global Includes
 */
require_once("../app/config/dirconf.cfg.php");
require_once(DIR_BASE."app/main.inc.php");

try {
    // Handle Request
    $request = RequestHandler::createRequest();

    if (!$request->getRoute()->getCaller()) {
        // set loginpage to default
        $page = new Page();
        $page->nav->redirect("Page/Common/Login");
    }

    // Check if the page-value is valid
    SystemManager::validatePHPFilePath($request);

    switch ($request->getRoute()->getCaller()) {
        default:

            $classname = $request->getRoute()->getClassname();

            $page = new $classname($request);

            $page->render();
            break;
    }

} catch (Exception $e) {
    $systemConfig = Registry::getMainConfig();

    echo "<fieldset style='color: #000; border-color: #FF0000; background-color: #ffd0c0; border-width:thin; border-style:solid'>";
    echo "<legend style='padding:2px 5px'><strong>Exception</strong></legend>";
    echo nl2br($e->getMessage());
    echo "</fieldset>";
var_dump($e);
    if ($systemConfig && $systemConfig->get("debugException", 0)) {
        echo "<fieldset style='color: #000; border-color: #880000; background-color: #ffeab0; border-width:thin; border-style:solid'>";
        echo "<legend style='padding:2px 5px'><strong>Debug</strong></legend>";
        echo nl2br($e->getTraceAsString());
        echo "</fieldset>";
    }
}

?>
