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
use Common\Controller\SessionStore,
    Main\Manager,
    Main\Controller\Link,
    Main\Controller\Page,
    Main\Controller\Config;

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

    // Prepare Systemwide Config
    $config = new Config;

    // check for page- or popup-argument
    $outputfile = array();
    if (isset($_GET['page'])) {
        $outputfile['page'] = $_GET['page'];
    } elseif (isset($_GET['popup'])) {
        $outputfile['popup'] = $_GET['popup'];
    } else {
        // Clear Cache
        SessionStore::pruneCache();

        // set loginpage to default
        $page = new Page();
        $page->nav->redirect("page=common/login");
    }

    // Check if the page-value is valid
    $realpath = Manager\System::validatePHPFilePath(current($outputfile));

    switch (current($outputfile)) {
        default:
            /**
             * Start Database Transaction
             */
            $database = getDBInstance();

            if ($config->get("useTransactions", 1) && $database->getDatabasePlatform()->supportsTransactions()) {
                $database->beginTransaction();
            }

            /**
             * Page Header
             */
            Manager\Module::callModule(Manager\Module::EVENT_PRE_PAGEHEADER);
            include(DIR_MAIN."Helpers/".key($outputfile).".header.php");

            /**
             * Page Content
             */
            Manager\Module::callModule(Manager\Module::EVENT_PRE_PAGECONTENT);
            include($realpath);

            /**
             * Page Footer
             */
            include(DIR_MAIN."Helpers/".key($outputfile).".footer.php");

            /**
             * Commit Database Transaction
             */
            $database = getDBInstance();

            if ($database->isTransactionActive()) {
                // Commit Database-Changes
                $database->commit();
            }
            break;
    }

} catch (Exception $e) {
    if (isset($database) && $database->isTransactionActive()) {
        // Rollback Database-Changes
        $database->rollback();
    }

    echo "<fieldset style='color: #000; border-color: #FF0000; background-color: #ffd0c0; border-width:thin; border-style:solid'>";
    echo "<legend style='padding:2px 5px'><strong>Exception</strong></legend>";
    echo nl2br($e->getMessage());
    echo "</fieldset>";
var_dump($e);
    if (isset($config) && $config instanceof Config && $config->get("debugException", 0)) {
        echo "<fieldset style='color: #000; border-color: #880000; background-color: #ffeab0; border-width:thin; border-style:solid'>";
        echo "<legend style='padding:2px 5px'><strong>Debug</strong></legend>";
        echo nl2br($e->getTraceAsString());
        echo "</fieldset>";
    }
}

?>
