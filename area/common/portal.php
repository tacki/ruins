<?php
/**
 * Portal Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: portal.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Page Content
 */
$page->set("pagetitle", "Portal");
$page->set("headtitle", "Portal to ruins");

$page->nav->add(new Link("Allgemein"));
$page->nav->add(new Link("Aktualisieren", $page->url));
$page->nav->add(new Link("Logout", "page=common/logout"));

switch ($_GET['op']) {

    default:
        if ($user->settings->default_character->id > 0) {
            $page->nav->redirect("page=common/portal&op=forward");
        }

        $page->output("Deine Charaktere:`n`n");

        $characters = Manager\User::getUserCharactersList($user->id);

        if (!$characters) {
            $page->output("Keinen Charakter gefunden!");
            break;
        }

        $page->addSimpleTable("chartable");
        $page->addForm("charchooseform");
        $page->charchooseform->head("charchoose", "page=common/portal&op=forward");
        $page->nav->add(new Link("", "page=common/portal&op=forward"));

        foreach ($characters as $charid) {
            $page->chartable->startRow();
            $page->chartable->startData();
            $page->charchooseform->radio("chooser", $charid, ($user->current_character == $charid?true:false));

            $page->chartable->startData();

            $chartype = Manager\User::getCharacterType($charid);
            $character = new $chartype;
            $character->load($charid);

            $page->output("Name: " . $character->displayname . "`n");
            $page->output("Rasse: " . $character->race . "`n");
            $page->output("Beruf: " . $character->profession . "`n");
            $page->output("Geschlecht: " . $character->sex . "`n");
            $page->output("Level: " . $character->level . "`n");
            $curnav = explode("&", $character->current_nav);
            $page->output("Ort: " . Manager\System::translate($curnav[0]) . "`n");

            $page->output("`n");

            $page->chartable->closeRow();
        }

        $page->chartable->close();

        $page->charchooseform->setCSS("button");
        $page->charchooseform->submitButton("Weiter");
        $page->charchooseform->close();
        break;

    case "forward":
        // set new current character
        if (isset($_POST['chooser'])) {
            $user->current_character = $_POST['chooser'];
        } else {
            $user->character = $user->settings->default_character;
        }
        // load the new character
        //$user->loadCharacter();
        // we need to let the system know, that this user is now the loggedin one
        //$user->char->login();
        $user->character->loggedin = true;
        // Write to Debuglog
        $user->addDebugLog("Character choosen: ". $user->character->name);

        // Create new Page for the new Character
        // (updates page-class to use correct allowednavs, etc)
        $page = new Page($user->character);

        if ($page->cacheExists()) {
            $page->nav->loadFromCache();

            // Check if the Cached Navigation has a refresh-nav
            if ($page->nav->checkRequestURL($user->char->current_nav, true)) {
                // Redirect to current_nav to fetch a new version of the Page
                $page->nav->redirect($user->char->current_nav);
            }
        } else {
            // redirect to the last place visited
            $page->nav->redirect($user->character->current_nav);
        }

        break;
}

?>
