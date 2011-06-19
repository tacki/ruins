<?php
/**
 * Support Popup
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Common\Controller\SessionStore,
    Main\Controller\Link,
    Main\Controller\Page,
    Main\Entities,
    Main\Manager;

/**
 * Page Content
 */
$popup->set("pagetitle", "Supportanfrage");
$popup->set("headtitle", "Supportanfrage");

$popup->nav->addLink("Anfrage", $popup->url);

if (isset($user->character) && $user->character->loggedin) {
    $loggedin = true;
} else {
    $loggedin = false;
}

switch ($_GET['op']) {

    default:
        $popup->addForm("support");
        $popup->getForm("support")->head("supportform", "popup=Popup/Support&op=request");

        $popup->addSimpleTable("supportformtable");
        $popup->getForm("support")->setCSS("input");

        // Login Name
        $popup->getSimpleTable("supportformtable")->startRow();
        $popup->getSimpleTable("supportformtable")->startData();
        $popup->output("Loginname: ");
        $popup->getSimpleTable("supportformtable")->startData();
        if ($loggedin) {
            $popup->getForm("support")->inputText("userlogin", $user->login, 20, 50, true);
        } else {
            $popup->getForm("support")->inputText("userlogin");
        }
        $popup->getSimpleTable("supportformtable")->closeRow();

        // Email
        $popup->getSimpleTable("supportformtable")->startRow();
        $popup->getSimpleTable("supportformtable")->startData();
        $popup->output("Email Addresse: ");
        $popup->getSimpleTable("supportformtable")->startData();
        if ($loggedin) {
            $popup->getForm("support")->inputText("email", $user->email, 20, 50, true);
        } else {
            $popup->getForm("support")->inputText("email");
        }
        $popup->getSimpleTable("supportformtable")->closeRow();

        // Character
        $popup->getSimpleTable("supportformtable")->startRow();
        $popup->getSimpleTable("supportformtable")->startData();
        $popup->output("Character: ");
        $popup->getSimpleTable("supportformtable")->startData();
        if ($loggedin) {
            $popup->getForm("support")->inputText("charname", $user->character->name, 20, 50, true);
        } else {
            $popup->getForm("support")->inputText("charname");
        }
        $popup->getSimpleTable("supportformtable")->closeRow();

        // Supporttext
        $popup->getSimpleTable("supportformtable")->startRow();
        $popup->getSimpleTable("supportformtable")->startData();
        $popup->output("Supportanfrage: ");
        $popup->getSimpleTable("supportformtable")->startData();
        $popup->getForm("support")->textArea("text", false, 45);
        $popup->getSimpleTable("supportformtable")->closeRow();

        // CAPTCHA
        $popup->getSimpleTable("supportformtable")->startRow();
        $popup->getSimpleTable("supportformtable")->startData();
        $popup->output("Botschutz: ");
        $popup->getSimpleTable("supportformtable")->startData();
        $popup->output("<img src='".Manager\System::getOverloadedFilePath("/Helpers/captcha.php", true)."'>", true);
        $popup->getForm("support")->inputText("captcha", false, 5, 5);
        $popup->getSimpleTable("supportformtable")->closeRow();

        // Pagedump
        if ($loggedin) {
            $popup->getSimpleTable("supportformtable")->startRow();
            $popup->getSimpleTable("supportformtable")->startData();
            $popup->output("Seitenkopie`neinfügen: ");
            $popup->getSimpleTable("supportformtable")->startData();
            $popup->getForm("support")->checkbox("pagedump");
            $popup->getSimpleTable("supportformtable")->closeRow();
        }

        // Submitbutton
        $popup->getSimpleTable("supportformtable")->startRow();
        $popup->getSimpleTable("supportformtable")->startData(false, 2);
        $popup->getForm("support")->setCSS("button");
        $popup->getForm("support")->submitButton("Absenden");

        $popup->getSimpleTable("supportformtable")->close();
        $popup->getForm("support")->close();
        break;

    case "request":
        // Captcha Check
        if ($_POST['captcha'] !== SessionStore::get("support_captcha")) {
            $popup->output("Falscher Botschutz-Code eingegeben!`n`n");
            $popup->nav->addTextLink("Zurück", "popup=Popup/Support");
            break;
        }
        SessionStore::remove("support_captcha");

        // Valid Supportrequest Check
        if (!$loggedin) {
            if (!$_POST['userlogin'] || !$_POST['email'] || !$_POST['text']) {
                $popup->output("Bitte alle Felder ausfüllen!`n`n");
                $popup->nav->addTextLink("Zurück", "popup=Popup/Support");
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

        $data = new \Modules\Support\Entities\SupportRequests;

        if ($loggedin) {
            $data->user = $user;
        } elseif ($user = $em->getRepository("Main:User")->findByLogin($_POST['userlogin'])) {
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

        $popup->addForm("support");
        $popup->getForm("support")->head("supportform", "popup=Popup/Support");
        $popup->output("<div class='floatclear center'>", true);
        $popup->getForm("support")->setCSS("button");
        $popup->getForm("support")->submitButton("Zurück");
        $popup->getForm("support")->close();
        $popup->output("</div>", true);
        break;
}
?>
