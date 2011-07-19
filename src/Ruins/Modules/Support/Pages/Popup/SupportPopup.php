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
namespace Ruins\Modules\Support\Pages\Popup;
use Ruins\Main\Controller\Popup;
use Ruins\Common\Controller\SessionStore;
use Ruins\Main\Controller\Link;
use Ruins\Main\Controller\Page;
use Ruins\Modules\Support\Entities\SupportRequests;
use Ruins\Main\Manager\SystemManager;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class SupportPopup extends AbstractPageObject
{
    public $title  = "Supportanfrage";

    public function createContent($page, $parameters)
    {
        $user = $this->getUser();

        switch ($parameters['op']) {

            default:
                $page->addForm("support");
                $page->getForm("support")->head("supportform", "Popup/Support/request");

                $page->addSimpleTable("supportformtable");
                $page->getForm("support")->setCSS("input");

                // Login Name
                $page->getSimpleTable("supportformtable")->startData();
                $page->output("Loginname: ");
                $page->getSimpleTable("supportformtable")->startData();
                if ($user->character) {
                    $page->getForm("support")->inputText("userlogin", $user->login, 20, 50, true);
                } else {
                    $page->getForm("support")->inputText("userlogin");
                }
                $page->getSimpleTable("supportformtable")->closeRow();

                // Email
                $page->getSimpleTable("supportformtable")->startData();
                $page->output("Email Addresse: ");
                $page->getSimpleTable("supportformtable")->startData();
                if ($loggedin) {
                    $page->getForm("support")->inputText("email", $user->email, 20, 50, true);
                } else {
                    $page->getForm("support")->inputText("email");
                }
                $page->getSimpleTable("supportformtable")->closeRow();

                // Character
                $page->getSimpleTable("supportformtable")->startData();
                $page->output("Character: ");
                $page->getSimpleTable("supportformtable")->startData();
                if ($loggedin) {
                    $page->getForm("support")->inputText("charname", $user->character->name, 20, 50, true);
                } else {
                    $page->getForm("support")->inputText("charname");
                }
                $page->getSimpleTable("supportformtable")->closeRow();

                // Supporttext
                $page->getSimpleTable("supportformtable")->startData();
                $page->output("Supportanfrage: ");
                $page->getSimpleTable("supportformtable")->startData();
                $page->getForm("support")->textArea("text", false, 45);
                $page->getSimpleTable("supportformtable")->closeRow();

                // CAPTCHA
                $page->getSimpleTable("supportformtable")->startData();
                $page->output("Botschutz: ");
                $page->getSimpleTable("supportformtable")->startData();
                $page->output("<img src='".SystemManager::getOverloadedFilePath("/Helpers/captcha.php", true)."'>", true);
                $page->getForm("support")->inputText("captcha", false, 5, 5);
                $page->getSimpleTable("supportformtable")->closeRow();

                // Pagedump
                if ($loggedin) {
                    $page->getSimpleTable("supportformtable")->startData();
                    $page->output("Seitenkopie`neinfügen: ");
                    $page->getSimpleTable("supportformtable")->startData();
                    $page->getForm("support")->checkbox("pagedump");
                    $page->getSimpleTable("supportformtable")->closeRow();
                }

                // Submitbutton
                $page->getSimpleTable("supportformtable")->startData(false, 2);
                $page->getForm("support")->setCSS("button");
                $page->getForm("support")->submitButton("Absenden");

                $page->getSimpleTable("supportformtable")->close();
                $page->getForm("support")->close();
                break;

            case "request":
                // Captcha Check
                if ($parameters['captcha'] !== SessionStore::get("support_captcha")) {
                    $page->output("Falscher Botschutz-Code eingegeben!`n`n");
                    $page->nav->addTextLink("Zurück", "Popup/Support");
                    break;
                }
                //SessionStore::remove("support_captcha");

                // Valid Supportrequest Check
                if (!$loggedin) {
                    if (!$parameters['userlogin'] || !$parameters['email'] || !$parameters['text']) {
                        $page->output("Bitte alle Felder ausfüllen!`n`n");
                        $page->nav->addTextLink("Zurück", "Popup/Support");
                        break;
                    }
                }

                // Get Pagedump of the Mainpage
                // To get this, we need to create a new, temporary Page-Object,
                // initialize it with the current character (to get the correct Template)
                // and call Page::getLatestGenerated()
                if (isset($parameters['pagedump']) && $loggedin) {
                    $temppage = new Page($user->character);
                    $pagedump = $temppage->getLatestGenerated();
                } else {
                    $pagedump = "-";
                }

                // Collect all Information and write it to the Database
                $em = Registry::getEntityManager();

                $data = new SupportRequests;

                if ($loggedin) {
                    $data->user = $user;
                } elseif ($user = $em->getRepository("Main:User")->findByLogin($parameters['userlogin'])) {
                    $data->user = $user;
                }

                $data->email         = $parameters['email'];
                $data->charactername = $parameters['charname'];
                $data->text          = $parameters['text'];
                $data->pagedump      = $pagedump;
                $em->persist($data);

                $em->flush();

                if ($data->id) {
                    $page->output("Supportanfrage abgeschickt!`n`n");
                } else {
                    $page->output("Fehler beim Speichern der Supportanfrage! :(`n`n");
                }

                $page->addForm("support");
                $page->getForm("support")->head("supportform", "Popup/Support");
                $page->output("<div class='floatclear center'>", true);
                $page->getForm("support")->setCSS("button");
                $page->getForm("support")->submitButton("Zurück");
                $page->getForm("support")->close();
                $page->output("</div>", true);
                break;
        }
    }
}