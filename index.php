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
use Common\Controller\SessionStore;
use Main\Manager\SystemManager;
use Main\Manager\ModuleManager;
use Main\Controller\Link;
use Main\Controller\Page;
use Common\Controller\Registry;

/**
 * Global Includes
 */
require_once("config/dirconf.cfg.php");
require_once(DIR_BASE."main.inc.php");

try {
    // set op-value if it's not set
    if (!isset($_GET['op'])) {
        $_GET['op'] = NULL;
    }

    // check for page- or popup-argument
    $outputfile = array();
    if (isset($_GET['page'])) {
        $outputfile['page'] = $_GET['page'];
    } elseif (isset($_GET['popup'])) {
        $outputfile['popup'] = $_GET['popup'];
    } else {
        // set loginpage to default
        $page = new Page();
        $page->nav->redirect("page=common/login");
    }

    // Check if the page-value is valid
    $realpath = SystemManager::validatePHPFilePath(current($outputfile));

    switch (current($outputfile)) {
        default:
            /**
             * Page Header
             */
            ModuleManager::callModule(ModuleManager::EVENT_PRE_PAGEHEADER);
            include(DIR_MAIN."Helpers/".key($outputfile).".header.php");

            /**
             * Page Content
             */
            ModuleManager::callModule(ModuleManager::EVENT_PRE_PAGECONTENT);
            include($realpath);

            /**
             * Page Footer
             */
            include(DIR_MAIN."Helpers/".key($outputfile).".footer.php");
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
