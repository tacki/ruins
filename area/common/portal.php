<?php
/**
 * Portal Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Link,
    Main\Controller\Page,
    Main\Manager;

/**
 * Page Content
 */
$page->set("pagetitle", "Portal");
$page->set("headtitle", "Portal to ruins");

$page->nav->addHead("Allgemein")
          ->addLink("Aktualisieren", $page->url)
          ->addLink("Logout", "page=common/logout");

switch ($_GET['op']) {

    default:
        if (!$user) {
            $page->nav->redirect("page=common/login");
        }

        if ($user->settings->default_character) {
            $page->nav->redirect("page=common/portal&op=forward");
        }

        $page->output("Deine Charaktere:`n`n");

        $characters = Manager\User::getUserCharactersList($user);

        if (!$characters) {
            $page->output("Keinen Charakter gefunden!");
            break;
        }

        $page->addSimpleTable("chartable");
        $page->addForm("charchooseform");
        $page->getForm("charchooseform")->head("charchoose", "page=common/portal&op=forward");
        $page->nav->addHiddenLink("page=common/portal&op=forward");

        foreach ($characters as $character) {
            $page->getSimpleTable("chartable")->startRow();
            $page->getSimpleTable("chartable")->startData();
            $page->getForm("charchooseform")->radio("chooser", $character->id, ($user->current_character == $character?true:false));

            $page->getSimpleTable("chartable")->startData();

            $page->output("Name: " . $character->displayname . "`n");
            $page->output("Rasse: " . $character->race . "`n");
            $page->output("Beruf: " . $character->profession . "`n");
            $page->output("Geschlecht: " . $character->sex . "`n");
            $page->output("Level: " . $character->level . "`n");
            $curnav = explode("&", $character->current_nav);
            $page->output("Ort: " . Manager\System::translate($curnav[0]) . "`n");

            $page->output("`n");

            $page->getSimpleTable("chartable")->closeRow();
        }

        $page->getSimpleTable("chartable")->close();

        $page->getForm("charchooseform")->setCSS("button");
        $page->getForm("charchooseform")->submitButton("Weiter");
        $page->getForm("charchooseform")->close();
        break;

    case "forward":
        // set new current character
        if (isset($_POST['chooser'])) {
            $user->character = $em->find("Main:Character", $_POST['chooser']);
        } else {
            $user->character = $user->settings->default_character;
        }

        // we need to let the system know, that this character is now the loggedin one
        $user->character->login();

        // Write to Debuglog
        $user->addDebugLog("Character choosen: ". $user->character->name);

        // Create new Page for the new Character
        // (updates page-class to use correct allowednavs, etc)
        $page = new Page($user->character);

        if ($page->cacheExists()) {
            $page->nav->loadFromCache();

            // Check if the Cached Navigation has a refresh-nav
            if ($page->nav->checkRequestURL($user->character->current_nav, true)) {
                // Redirect to current_nav to fetch a new version of the Page
                $page->nav->redirect($user->character->current_nav);
            }
        } else {
            // redirect to the last place visited
            $page->nav->redirect($user->character->current_nav);
        }

        break;
}

?>
