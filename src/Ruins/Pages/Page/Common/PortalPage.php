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
use Ruins\Main\Controller\Page;
use Ruins\Common\Interfaces\PageObjectInterface;
use Ruins\Common\Controller\Registry;

class PortalPage extends Page implements PageObjectInterface
{
    protected $pagetitle  = "Portal";

    public function setTitle()
    {
        $this->set("pagetitle", $this->pagetitle);
        $this->set("headtitle", $this->pagetitle);
    }

    public function createMenu()
    {
        $this->nav->addHead("Allgemein")
                  ->addLink("Aktualisieren", $this->url)
                  ->addLink("Logout", "Page/Common/LogoutPage");
    }

    public function createContent(array $parameters)
    {
        $user = Registry::getUser();

        switch ($parameters['op']) {

            default:
                if (!$user) {
                    $this->nav->redirect("Page/Common/LoginPage");
                }

                if ($user->settings->default_character) {
                    $this->nav->redirect("Page/Common/PortalPage/forward");
                }

                $this->output("Deine Charaktere:`n`n");

                $characters = $em->getRepository("Main:User")->getCharacters($user);

                if (!$characters) {
                    $this->output("Keinen Charakter gefunden!");
                    break;
                }

                $this->addSimpleTable("chartable");
                $this->addForm("charchooseform");
                $this->getForm("charchooseform")->head("charchoose", "Page/Common/PortalPage/forward");
                $this->nav->addHiddenLink("Page/Common/PortalPage/forward");

                foreach ($characters as $character) {
                    $this->getSimpleTable("chartable")->startRow();
                    $this->getSimpleTable("chartable")->startData();
                    $this->getForm("charchooseform")->radio("chooser", $character->id, ($user->current_character == $character?true:false));

                    $this->getSimpleTable("chartable")->startData();

                    $this->output("Name: " . $character->displayname . "`n");
                    $this->output("Rasse: " . $character->race . "`n");
                    $this->output("Beruf: " . $character->profession . "`n");
                    $this->output("Geschlecht: " . $character->sex . "`n");
                    $this->output("Level: " . $character->level . "`n");
                    $curnav = explode("&", $character->current_nav);
                    $this->output("Ort: " . SystemManager::translate($curnav[0]) . "`n");

                    $this->output("`n");

                    $this->getSimpleTable("chartable")->closeRow();
                }

                $this->getSimpleTable("chartable")->close();

                $this->getForm("charchooseform")->setCSS("button");
                $this->getForm("charchooseform")->submitButton("Weiter");
                $this->getForm("charchooseform")->close();
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
                $this->__construct($user->character);

                if ($this->cacheExists()) {
                    $this->nav->loadFromCache();

                    // Check if the Cached Navigation has a refresh-nav
                    if ($this->nav->checkRequestURL($user->character->current_nav, true)) {
                        // Redirect to current_nav to fetch a new version of the Page
                        $this->nav->redirect($user->character->current_nav);
                    }
                } else {
                    // redirect to the last place visited
                    $this->nav->redirect($user->character->current_nav);
                }

                break;
        }
    }

}