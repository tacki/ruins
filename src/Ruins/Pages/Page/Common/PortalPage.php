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
namespace Ruins\Pages\Page\Common;
use Ruins\Main\Controller\Link;
use Ruins\Main\Manager\SystemManager;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class PortalPage extends AbstractPageObject
{
    public $title  = "Portal";

    public function createContent($page, $parameters)
    {
        $user = $this->getUser();

        $page->nav->addHead("Allgemein")
                  ->addLink("Aktualisieren", $page->url)
                  ->addLink("Logout", "Page/Common/Logout");

        switch ($parameters['op']) {

            default:
                if (!$user) {
                    $page->nav->redirect("Page/Common/Login");
                }

                if ($user->settings->default_character) {
                    $page->nav->redirect("Page/Common/Portal/forward");
                }

                $page->output("Deine Charaktere:`n`n");

                $characters = $em->getRepository("Main:User")->getCharacters($user);

                if (!$characters) {
                    $page->output("Keinen Charakter gefunden!");
                    break;
                }

                $page->addSimpleTable("chartable");
                $page->addForm("charchooseform");
                $page->getForm("charchooseform")->head("charchoose", "Page/Common/Portal/forward");
                $page->nav->addHiddenLink("Page/Common/Portal/forward");

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
                    $page->output("Ort: " . SystemManager::translate($curnav[0]) . "`n");

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
                if (isset($parameters['chooser'])) {
                    $user->character = $em->find("Main:Character", $parameters['chooser']);
                } else {
                    $user->character = $user->settings->default_character;
                }

                // we need to let the system know, that this character is now the loggedin one
                $user->character->login();

                // Write to Debuglog
                $user->addDebugLog("Character choosen: ". $user->character->name);

                // Create new Page for the new Character
                // (updates page-class to use correct allowednavs, etc)
                $page->__construct($user->character);

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
    }

}