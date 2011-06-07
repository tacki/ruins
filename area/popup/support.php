<?php
/**
 * Support Popup
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: support.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Page Content
 */
$popup->set("pagetitle", "Supportanfrage");
$popup->set("headtitle", "Supportanfrage");

$popup->nav->add(new Link("Anfrage", "popup=popup/messenger&op=create"));

if (isset($user) && $user->loggedin) {
    $loggedin = true;
} else {
    $loggedin = false;
}

switch ($_GET['op']) {

    default:
        $popup->addForm("supportform");
        $popup->supportform->head("supportform", "popup=popup/support&op=request");

        $popup->addSimpleTable("supportformtable");
        $popup->supportform->setCSS("input");

        // Login Name
        $popup->supportformtable->startRow();
        $popup->supportformtable->startData();
        $popup->output("Loginname: ");
        $popup->supportformtable->startData();
        if ($loggedin) {
            $popup->supportform->inputText("userlogin", $user->login, 20, 50, true);
        } else {
            $popup->supportform->inputText("userlogin");
        }
        $popup->supportformtable->closeRow();

        // Email
        $popup->supportformtable->startRow();
        $popup->supportformtable->startData();
        $popup->output("Email Addresse: ");
        $popup->supportformtable->startData();
        if ($loggedin) {
            $popup->supportform->inputText("email", $user->email, 20, 50, true);
        } else {
            $popup->supportform->inputText("email");
        }
        $popup->supportformtable->closeRow();

        // Character
        $popup->supportformtable->startRow();
        $popup->supportformtable->startData();
        $popup->output("Character: ");
        $popup->supportformtable->startData();
        if ($loggedin) {
            $popup->supportform->inputText("charname", $user->character->name, 20, 50, true);
        } else {
            $popup->supportform->inputText("charname");
        }
        $popup->supportformtable->closeRow();

        // Supporttext
        $popup->supportformtable->startRow();
        $popup->supportformtable->startData();
        $popup->output("Supportanfrage: ");
        $popup->supportformtable->startData();
        $popup->supportform->textArea("text", false, 45);
        $popup->supportformtable->closeRow();

        // CAPTCHA
        $popup->supportformtable->startRow();
        $popup->supportformtable->startData();
        $popup->output("Botschutz: ");
        $popup->supportformtable->startData();
        $popup->output("<img src='includes/helpers/captcha.php'>", true);
        $popup->supportform->inputText("captcha", false, 5, 5);
        $popup->supportformtable->closeRow();

        // Pagedump
        if ($loggedin) {
            $popup->supportformtable->startRow();
            $popup->supportformtable->startData();
            $popup->output("Seitenkopie`neinfügen: ");
            $popup->supportformtable->startData();
            $popup->supportform->checkbox("pagedump");
            $popup->supportformtable->closeRow();
        }

        // Submitbutton
        $popup->supportformtable->startRow();
        $popup->supportformtable->startData(false, 2);
        $popup->supportform->setCSS("button");
        $popup->supportform->submitButton("Absenden");

        $popup->supportformtable->close();
        $popup->supportform->close();
        break;

    case "request":
        // Captcha Check
        if ($_POST['captcha'] !== SessionStore::get("support_captcha")) {
            $popup->output("Falscher Botschutz-Code eingegeben!`n`n");
            $popup->nav->addTextLink(new Link("Zurück", "popup=popup/support"));
            break;
        }
        SessionStore::remove("support_captcha");

        // Valid Supportrequest Check
        if (!$loggedin) {
            if (!$_POST['userlogin'] || !$_POST['email'] || !$_POST['text']) {
                $popup->output("Bitte alle Felder ausfüllen!`n`n");
                $popup->nav->addTextLink(new Link("Zurück", "popup=popup/support"));
                break;
            }
        }

        // Get Pagedump of the Mainpage
        // To get this, we need to create a new, temporary Page-Object,
        // initialize it with the current character (to get the correct Template)
        // and call Page::getLatestGenerated()
        if (isset($_POST['pagedump']) && $loggedin) {
            $temppage = new Page($user->character);
            $pagedump = $temppage->getLatestGenerated();
        } else {
            $pagedump = "-";
        }

        // Collect all Information and write it to the Database
        global $em;

        $data = new Entities\SupportRequests;

        if ($loggedin) {
            $data->user = $user->getEntity();
        } elseif ($user = $em->getRepository("Entities\User")->findByLogin($_POST['userlogin'])) {
            $data->user = $user;
        }

        $data->email         = $_POST['email'];
        $data->charactername = $_POST['charname'];
        $data->text          = $_POST['text'];
        $data->pagedump      = $pagedump;
        $em->persist($data);

        $em->flush();

        if ($data->id) {
            $popup->output("Supportanfrage abgeschickt!`n`n");
        } else {
            $popup->output("Fehler beim Speichern der Supportanfrage! :(`n`n");
        }

        $popup->addForm("supportform");
        $popup->supportform->head("supportform", "popup=popup/support");
        $popup->output("<div class='floatclear center'>", true);
        $popup->supportform->setCSS("button");
        $popup->supportform->submitButton("Zurück");
        $popup->supportform->close();
        $popup->output("</div>", true);
        break;
}
?>
