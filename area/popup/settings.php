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
use Controller\Link;

/**
 * Page Content
 */
$popup->set("pagetitle", "Einstellungen");
$popup->set("headtitle", "Einstellungen");

$popup->nav->add(new Link("Benutzer", "popup=popup/settings&op=user"));
$popup->nav->add(new Link("Charakter", "popup=popup/settings&op=char"));
$popup->nav->add(new Link("Sonstiges", "popup=popup/settings&op=other"));

$popup->addForm("settingsform");

switch ($_GET['op']) {

    default:
    case "user":
        $popup->settingsform->head("settingsform", "popup=popup/settings&op=change");
        $popup->settingsform->hidden("section", "user");

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
        $popup->settingsform->inputText("email", $user->email, 20, 50);
        $popup->output("</div>", true);

        // Password
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Passwort: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->settingsform->inputPassword("password", "", 20, 50);
        $popup->output("</div>", true);

        break;

    case "char":
        $popup->settingsform->head("settingsform", "popup=popup/settings&op=change");
        $popup->settingsform->hidden("section", "char");

        // Default Character
        $defaultchar 	= $user->settings->default_character;
        $charlist		= Manager\User::getUserCharactersList($user->id);

        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Standard Charakter: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->settingsform->selectStart("default_character", 1);
        $popup->settingsform->selectOption("Keiner", 0);
        foreach ($charlist as $character) {
            if ($character === $defaultchar) {
                $popup->settingsform->selectOption($character->name, $character->id, true);
            } else {
                $popup->settingsform->selectOption($character->name, $character->id);
            }
        }
        $popup->settingsform->selectEnd();
        $popup->output("</div>", true);

        break;

    case "other":
        $popup->settingsform->head("settingsform", "popup=popup/settings&op=change");
        $popup->settingsform->hidden("section", "other");

        // Chatcensorship
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Chat Zensur: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->settingsform->checkbox("chat_censorship", false, false, $user->settings->chat_censorship);
        $popup->output("</div>", true);

        // Chatdateformat
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Chat Datums Format: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->settingsform->inputText("chat_dateformat", $user->settings->chat_dateformat, 20, 50);
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
                    $user->password = md5($_POST['password']);
                }
                break;

            case "char":
                // Default Character
                if (is_numeric($_POST['default_character'])) {
                    global $em;
                    $user->settings->default_character = $em->find("Entities\Character",$_POST['default_character']);
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

        $popup->settingsform->head("settingsform", "popup=popup/settings");
        $popup->output("<div class='floatclear center'>", true);
        $popup->settingsform->setCSS("button");
        $popup->settingsform->submitButton("Zurück");
        $popup->settingsform->close();
        $popup->output("</div>", true);
        break;
}

if ($_GET['op'] != "change") {
    // Submitbutton
    $popup->output("<div class='floatclear center'>", true);
    $popup->settingsform->setCSS("button");
    $popup->settingsform->submitButton("Speichern");
    $popup->output("</div>", true);

    $popup->settingsform->close();
}
?>
