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
use Ruins\Main\Controller\Link;
use Ruins\Common\Controller\Registry;

/**
 * Page Content
 */
$popup->set("pagetitle", "Einstellungen");
$popup->set("headtitle", "Einstellungen");

$popup->nav->addLink("Benutzer", "popup=popup/settings&op=user")
           ->addLink("Charakter", "popup=popup/settings&op=char")
           ->addLink("Sonstiges", "popup=popup/settings&op=other");

$popup->addForm("settings");

$em = Registry::getEntityManager();

switch ($_GET['op']) {

    default:
    case "user":
        $popup->getForm("settings")->head("settingsform", "popup=popup/settings&op=change");
        $popup->getForm("settings")->hidden("section", "user");

        // Login Name
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Loginname: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->output($user->login);
        $popup->output("</div>", true);

        // Email
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("EMail: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->getForm("settings")->inputText("email", $user->email, 20, 50);
        $popup->output("</div>", true);

        // Password
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Passwort: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->getForm("settings")->inputPassword("password", "", 20, 50);
        $popup->output("</div>", true);

        break;

    case "char":
        $popup->getForm("settings")->head("settingsform", "popup=popup/settings&op=change");
        $popup->getForm("settings")->hidden("section", "char");

        // Default Character
        $defaultchar 	= $user->settings->default_character;
        $charlist		= $em->getRepository("Main:User")->getCharacters($user);

        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Standard Charakter: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->getForm("settings")->selectStart("default_character", 1);
        $popup->getForm("settings")->selectOption("Keiner", 0);
        foreach ($charlist as $character) {
            if ($character === $defaultchar) {
                $popup->getForm("settings")->selectOption($character->name, $character->id, true);
            } else {
                $popup->getForm("settings")->selectOption($character->name, $character->id);
            }
        }
        $popup->getForm("settings")->selectEnd();
        $popup->output("</div>", true);

        break;

    case "other":
        $popup->getForm("settings")->head("settingsform", "popup=popup/settings&op=change");
        $popup->getForm("settings")->hidden("section", "other");

        // Chatcensorship
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Chat Zensur: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->getForm("settings")->checkbox("chat_censorship", false, false, $user->settings->chat_censorship);
        $popup->output("</div>", true);

        // Chatdateformat
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Chat Datums Format: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->getForm("settings")->inputText("chat_dateformat", $user->settings->chat_dateformat, 20, 50);
        $popup->output("</div>", true);

        break;

    case "change":
        switch ($_POST['section']) {
            case "user":
                // Email
                if (strlen($_POST['email']) > 0) {
                    $user->email = $_POST['email'];
                }

                // Password
                if (strlen($_POST['password']) > 0) {
                    $user->password = $em->getRepository("Main:User")->hashPassword($_POST['password']);
                }
                break;

            case "char":
                // Default Character
                if (is_numeric($_POST['default_character'])) {
                    $user->settings->default_character = $em->find("Main:Character",$_POST['default_character']);
                } else {
                    $user->settings->default_character = NULL;
                }
                break;

            case "other":
                // Chatcensorship
                $user->settings->chat_censorship = isset($_POST['chat_censorship']);

                // Chatdateformat
                if (strlen($_POST['chat_dateformat']) > 0) {
                    $user->settings->chat_dateformat = $_POST['chat_dateformat'];
                }
                break;
        }

        $popup->output("Einstellungen angepasst!");

        $popup->getForm("settings")->head("settingsform", "popup=popup/settings");
        $popup->output("<div class='floatclear center'>", true);
        $popup->getForm("settings")->setCSS("button");
        $popup->getForm("settings")->submitButton("Zurück");
        $popup->getForm("settings")->close();
        $popup->output("</div>", true);
        break;
}

if ($_GET['op'] != "change") {
    // Submitbutton
    $popup->output("<div class='floatclear center'>", true);
    $popup->getForm("settings")->setCSS("button");
    $popup->getForm("settings")->submitButton("Speichern");
    $popup->output("</div>", true);

    $popup->getForm("settings")->close();
}
?>
