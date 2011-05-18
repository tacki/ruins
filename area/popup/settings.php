<?php
/**
 * Settings Popup
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id$
 * @package Ruins
 */

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
        $charlist		= UserSystem::getUserCharactersList($user->id);
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Standard Charakter: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->settingsform->selectStart("default_character", 1);
        $popup->settingsform->selectOption("Keiner", 0);
        foreach ($charlist as $charid) {
            if ($charid == $defaultchar) {
                $popup->settingsform->selectOption(UserSystem::getCharacterName($charid, false), $charid, true);
            } else {
                $popup->settingsform->selectOption(UserSystem::getCharacterName($charid, false), $charid);
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
        $popup->settingsform->checkbox("chatcensorship", false, false, $user->settings->get("chatcensorship", 1));
        $popup->output("</div>", true);

        // Chatdateformat
        $popup->output("<div class='floatclear floatleft'>", true);
        $popup->output("Chat Datums Format: ");
        $popup->output("</div><div class='floatright'>", true);
        $popup->settingsform->inputText("chatdateformat", $user->settings->get("chatdateformat", "[H:i:s]"), 20, 50);
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
                    $user->settings->default_character = $_POST['default_character'];
                }
                break;

            case "other":
                // Chatcensorship
                if (isset($_POST['chatcensorship'])) {
                    $user->settings->chatcensorship = 1;
                } else {
                    $user->settings->chatcensorship = 0;
                }

                // Chatdateformat
                if (strlen($_POST['chatdateformat']) > 0) {
                    $user->settings->chatdateformat = $_POST['chatdateformat'];
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
