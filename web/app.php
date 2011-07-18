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
    $request = RequestHandler::getRequest();
    $requestInfo = SystemManager::getRequestFileInfo($request);

    if (!$request->getRouteCaller()) {
        // set loginpage to default
        $page = new Page();
        $page->nav->redirect("Page/Common/LoginPage");
    }

    // Check if the page-value is valid
    $realpath = SystemManager::validatePHPFilePath($request);

    switch ($request->getRouteCaller()) {
        default:
            /**
             * Page Header
             */
            ModuleManager::callModule(ModuleManager::EVENT_PRE_PAGEHEADER);
            include(DIR_MAIN."Helpers/".strtolower($request->getRouteCaller()).".header.php");

            /**
             * Page Content
             */
            ModuleManager::callModule(ModuleManager::EVENT_PRE_PAGECONTENT);
            $page = new $requestInfo['classname'];
            $page->create();
            $page->addCommonCSS("btcode.css");
            $page->addJavaScriptFile("timer.func.js");
            $page->addJavaScriptFile("global.func.js");
            $page->set("servertime", date("H:i:s"));
            $page->setTitle();
            $page->createMenu();
            $page->createContent($requestInfo['query']);
/*
            if ($request->getRouteCaller() == 'Popup') {
                $popup = new Ruins\Modules\Support\Pages\Popup\SupportPopup;

                $popup->addCommonCSS("btcode.css");
                $popup->addJavaScriptFile("timer.func.js");
                $popup->addJavaScriptFile("global.func.js");
                $popup->set("servertime", date("H:i:s"));
                Registry::set('main.output', $popup);

                //---

                $popup->create();
                $popup->setTitle();
                $popup->createMenu();
                $popup->createContent($requestInfo['query']);
            } else {
                include($realpath);
            }
*/
            /**
             * Page Footer
             */
            include(DIR_MAIN."Helpers/".strtolower($request->getRouteCaller()).".footer.php");
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
