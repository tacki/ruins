<?php
/**
 * Settings Popup
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Popup;
use Ruins\Main\Controller\Link;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class SettingsPopup extends AbstractPageObject
{
    public $title  = "Einstellungen";

    public function createContent($page, $parameters)
    {
        $page->getNavigation()->addLink("Benutzer", "Popup/Settings/user")
                   ->addLink("Charakter", "Popup/Settings/char")
                   ->addLink("Sonstiges", "Popup/Settings/other");

        $page->addForm("settings");

        $em = Registry::getEntityManager();

        switch ($parameters['op']) {

            default:
            case "user":
                $page->getForm("settings")->head("settingsform", "Popup/Settings/change");
                $page->getForm("settings")->hidden("section", "user");

                // Login Name
                $page->output("<div class='floatclear floatleft'>", true);
                $page->output("Loginname: ");
                $page->output("</div><div class='floatright'>", true);
                $page->output($user->login);
                $page->output("</div>", true);

                // Email
                $page->output("<div class='floatclear floatleft'>", true);
                $page->output("EMail: ");
                $page->output("</div><div class='floatright'>", true);
                $page->getForm("settings")->inputText("email", $user->email, 20, 50);
                $page->output("</div>", true);

                // Password
                $page->output("<div class='floatclear floatleft'>", true);
                $page->output("Passwort: ");
                $page->output("</div><div class='floatright'>", true);
                $page->getForm("settings")->inputPassword("password", "", 20, 50);
                $page->output("</div>", true);

                break;

            case "char":
                $page->getForm("settings")->head("settingsform", "Popup/Settings/change");
                $page->getForm("settings")->hidden("section", "char");

                // Default Character
                $defaultchar 	= $user->settings->default_character;
                $charlist		= $em->getRepository("Main:User")->getCharacters($user);

                $page->output("<div class='floatclear floatleft'>", true);
                $page->output("Standard Charakter: ");
                $page->output("</div><div class='floatright'>", true);
                $page->getForm("settings")->selectStart("default_character", 1);
                $page->getForm("settings")->selectOption("Keiner", 0);
                foreach ($charlist as $character) {
                    if ($character === $defaultchar) {
                        $page->getForm("settings")->selectOption($character->name, $character->id, true);
                    } else {
                        $page->getForm("settings")->selectOption($character->name, $character->id);
                    }
                }
                $page->getForm("settings")->selectEnd();
                $page->output("</div>", true);

                break;

            case "other":
                $page->getForm("settings")->head("settingsform", "Popup/Settings/change");
                $page->getForm("settings")->hidden("section", "other");

                // Chatcensorship
                $page->output("<div class='floatclear floatleft'>", true);
                $page->output("Chat Zensur: ");
                $page->output("</div><div class='floatright'>", true);
                $page->getForm("settings")->checkbox("chat_censorship", false, false, $user->settings->chat_censorship);
                $page->output("</div>", true);

                // Chatdateformat
                $page->output("<div class='floatclear floatleft'>", true);
                $page->output("Chat Datums Format: ");
                $page->output("</div><div class='floatright'>", true);
                $page->getForm("settings")->inputText("chat_dateformat", $user->settings->chat_dateformat, 20, 50);
                $page->output("</div>", true);

                break;

            case "change":
                switch ($parameters['section']) {
                    case "user":
                        // Email
                        if (strlen($parameters['email']) > 0) {
                            $user->email = $parameters['email'];
                        }

                        // Password
                        if (strlen($parameters['password']) > 0) {
                            $user->password = $em->getRepository("Main:User")->hashPassword($parameters['password']);
                        }
                        break;

                    case "char":
                        // Default Character
                        if (is_numeric($parameters['default_character'])) {
                            $user->settings->default_character = $em->find("Main:Character",$parameters['default_character']);
                        } else {
                            $user->settings->default_character = NULL;
                        }
                        break;

                    case "other":
                        // Chatcensorship
                        $user->settings->chat_censorship = isset($parameters['chat_censorship']);

                        // Chatdateformat
                        if (strlen($parameters['chat_dateformat']) > 0) {
                            $user->settings->chat_dateformat = $parameters['chat_dateformat'];
                        }
                        break;
                }

                $page->output("Einstellungen angepasst!");

                $page->getForm("settings")->head("settingsform", "Popup/Settings");
                $page->output("<div class='floatclear center'>", true);
                $page->getForm("settings")->setCSS("button");
                $page->getForm("settings")->submitButton("Zurück");
                $page->getForm("settings")->close();
                $page->output("</div>", true);
                break;
        }

        if ($parameters['op'] != "change") {
            // Submitbutton
            $page->output("<div class='floatclear center'>", true);
            $page->getForm("settings")->setCSS("button");
            $page->getForm("settings")->submitButton("Speichern");
            $page->output("</div>", true);

            $page->getForm("settings")->close();
        }
    }
}