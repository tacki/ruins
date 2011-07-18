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
use Ruins\Common\Interfaces\PageObjectInterface;

class SupportPopup extends Popup implements PageObjectInterface
{
    protected $pagetitle  = "Supportanfrage";

    public function setTitle()
    {
        $this->set("pagetitle", $this->pagetitle);
        $this->set("headtitle", $this->pagetitle);
    }

    public function createMenu()
    {
        $this->nav->addLink("Anfrage", $this->url);
    }

    public function createContent(array $parameters)
    {
        switch ($parameters['op']) {

            default:
            $this->addForm("support");
            $this->getForm("support")->head("supportform", "Popup/SupportPopup/request");

            $this->addSimpleTable("supportformtable");
            $this->getForm("support")->setCSS("input");

            // Login Name
            $this->getSimpleTable("supportformtable")->startData();
            $this->output("Loginname: ");
            $this->getSimpleTable("supportformtable")->startData();
            if ($this->_char) {
                $this->getForm("support")->inputText("userlogin", $user->login, 20, 50, true);
            } else {
                $this->getForm("support")->inputText("userlogin");
            }
            $this->getSimpleTable("supportformtable")->closeRow();

            // Email
            $this->getSimpleTable("supportformtable")->startData();
            $this->output("Email Addresse: ");
            $this->getSimpleTable("supportformtable")->startData();
            if ($loggedin) {
                $this->getForm("support")->inputText("email", $user->email, 20, 50, true);
            } else {
                $this->getForm("support")->inputText("email");
            }
            $this->getSimpleTable("supportformtable")->closeRow();

            // Character
            $this->getSimpleTable("supportformtable")->startData();
            $this->output("Character: ");
            $this->getSimpleTable("supportformtable")->startData();
            if ($loggedin) {
                $this->getForm("support")->inputText("charname", $user->character->name, 20, 50, true);
            } else {
                $this->getForm("support")->inputText("charname");
            }
            $this->getSimpleTable("supportformtable")->closeRow();

            // Supporttext
            $this->getSimpleTable("supportformtable")->startData();
            $this->output("Supportanfrage: ");
            $this->getSimpleTable("supportformtable")->startData();
            $this->getForm("support")->textArea("text", false, 45);
            $this->getSimpleTable("supportformtable")->closeRow();

            // CAPTCHA
            $this->getSimpleTable("supportformtable")->startData();
            $this->output("Botschutz: ");
            $this->getSimpleTable("supportformtable")->startData();
            $this->output("<img src='".SystemManager::getOverloadedFilePath("/Helpers/captcha.php", true)."'>", true);
            $this->getForm("support")->inputText("captcha", false, 5, 5);
            $this->getSimpleTable("supportformtable")->closeRow();

            // Pagedump
            if ($loggedin) {
                $this->getSimpleTable("supportformtable")->startData();
                $this->output("Seitenkopie`neinfügen: ");
                $this->getSimpleTable("supportformtable")->startData();
                $this->getForm("support")->checkbox("pagedump");
                $this->getSimpleTable("supportformtable")->closeRow();
            }

            // Submitbutton
            $this->getSimpleTable("supportformtable")->startData(false, 2);
            $this->getForm("support")->setCSS("button");
            $this->getForm("support")->submitButton("Absenden");

            $this->getSimpleTable("supportformtable")->close();
            $this->getForm("support")->close();
            break;

            case "request":
                // Captcha Check
                if ($_POST['captcha'] !== SessionStore::get("support_captcha")) {
                    $this->output("Falscher Botschutz-Code eingegeben!`n`n");
                    $this->nav->addTextLink("Zurück", "Popup/SupportPopup");
                    break;
                }
                //SessionStore::remove("support_captcha");

                // Valid Supportrequest Check
                if (!$loggedin) {
                    if (!$_POST['userlogin'] || !$_POST['email'] || !$_POST['text']) {
                        $this->output("Bitte alle Felder ausfüllen!`n`n");
                        $this->nav->addTextLink("Zurück", "Popup/SupportPopup");
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
                $em = Registry::getEntityManager();

                $data = new SupportRequests;

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
                    $this->output("Supportanfrage abgeschickt!`n`n");
                } else {
                    $this->output("Fehler beim Speichern der Supportanfrage! :(`n`n");
                }

                $this->addForm("support");
                $this->getForm("support")->head("supportform", "Popup/SupportPopup");
                $this->output("<div class='floatclear center'>", true);
                $this->getForm("support")->setCSS("button");
                $this->getForm("support")->submitButton("Zurück");
                $this->getForm("support")->close();
                $this->output("</div>", true);
                break;
        }
    }
}