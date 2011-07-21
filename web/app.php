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
use Ruins\Common\Controller\Registry;
use Ruins\Common\Manager\RequestManager;

/**
 * Global Includes
 */
require_once("../app/config/dirconf.cfg.php");
require_once(DIR_BASE."app/main.inc.php");

try {
    $opmode = "normal";

    // Create default Request
    $request = RequestManager::createRequest();

    // Check if there is a Requeststring
    if (strlen($request->getRouteAsString()) == 0) {
        // We are at our web-basedir, create new Request
        // for the Login-Page (which is default)
        $request = RequestManager::createRequest("Page/Common/Login");
    }

    // Try to load User
    if ($userid = SessionStore::get('userid')) {
        $user = Registry::getEntityManager()->find("Main:User", $userid);
        $user->prepare();

        Registry::setUser($user);
    }

    // Check if the Request is valid
    if (!$request->isValid()) {
        if (Registry::getUser()) {
            // load from cache
            $opmode = "cache";
            $request = RequestManager::createRequest("Page/Common/Empty");
        } else {
            // Create Request for 404-Page
            $request = RequestManager::createRequest("Page/Common/Error404");
        }
    }

    // Check if the requested Path is valid
    SystemManager::validatePHPFilePath($request);

    switch ($opmode) {
        case "normal":
            $page = $request->createPageObject();

            $page->render();
            break;

        case "cache":
            $page = $request->createPageObject();

            if ($page->renderFromCache()) {
                echo "~~~ From Cache (Validation Rule) ~~~";
                exit;
            } else {
                $page->redirect("Page/Common/Error404");
            }
            break;
    }

    Registry::getEntityManager()->flush();

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