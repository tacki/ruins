<?php
/**
 * Admin - Survey Module
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Modules\Survey\Pages\Page;
use Ruins\Modules\Survey\Manager\SurveyManager;
use Ruins\Common\Controller\Registry;

class SurveyPage extends Page
{
    protected $thistitle  = "Survey Module";

    public function setTitle()
    {
        $this->set("pagetitle", $this->pagetitle);
        $this->set("headtitle", $this->pagetitle);
    }

    public function createMenu()
    {
        $this->nav->addHead("Umfragen")
                  ->addLink("Übersicht", $this->url->base)
                  ->addLink("Erstellen", $this->url->base."/create")
                  ->addHead("Navigation")
                  ->addLink("Zurück zur Administration", "Page/Admin/Main");
    }

    public function createContent(array $parameters)
    {
        $em = Registry::getEntityManager();

        switch ($parameters['op']) {

            default:
            $polls = SurveyManager::getAllPolls(false);

            $this->output("`bUmfragen Liste`b `n`n");

            $this->addSimpleTable("surveylist")->head("100%", 1);

            // Header
            $this->getSimpleTable("surveylist")->startHead();
            $this->output("Id");
            $this->getSimpleTable("surveylist")->startHead();
            $this->output("Ersteller");
            $this->getSimpleTable("surveylist")->startHead();
            $this->output("Frage");
            $this->getSimpleTable("surveylist")->startHead();
            $this->output("Ablaufdatum");
            $this->getSimpleTable("surveylist")->startHead();
            $this->output("# Stimmen");
            $this->getSimpleTable("surveylist")->startHead();
            $this->output("Aktion");
            $this->getSimpleTable("surveylist")->closeRow();

            foreach ($polls as $poll) {
                // Id
                $this->getSimpleTable("surveylist")->startData(10);
                $this->output("`c".$poll->id."`c");

                // Creator
                $this->getSimpleTable("surveylist")->startData(100);
                $this->output("`c".$poll->creator->displayname."`c");

                // Question and Description
                $this->getSimpleTable("surveylist")->startData();
                $this->output($poll->question. "`n" . "`s".$poll->description."`s");

                // Deadline
                $this->getSimpleTable("surveylist")->startData(10);
                $this->output("`c".$poll->deadline->format("d.m.y")."`c");

                // # of Votes
                $this->getSimpleTable("surveylist")->startData(10);
                $this->output("`c".SurveyManager::getTotalNrOfVotes($poll)."`c");

                // Action
                $this->getSimpleTable("surveylist")->startData(50);
                $this->nav->addTextLink("Details", $this->url->base."/details/?pollId=$poll->id");
                if ($poll->active) {
                    $this->nav->addTextLink("Deaktivieren", $this->url->base."/deactivate/?pollId=$poll->id");
                } else {
                    $this->nav->addTextLink("Aktivieren", $this->url->base."/activate/?pollId=$poll->id");
                }
                $this->nav->addTextLink("Löschen", $this->url->base."/delete/?pollId=$poll->id");

                $this->getSimpleTable("surveylist")->closeRow();
            }
            $this->getSimpleTable("surveylist")->close();
            break;

            case "create":
                if (isset($_POST['question'])) {
                    $question     = $_POST['question'];
                    $description  = $_POST['description'];
                    $deadline     = $_POST['deadline'];
                    $answers = array_filter($_POST['answer']);


                    if (count($answers) > 1) {
                        $poll = SurveyManager::addPoll($question, $description, new DateTime($deadline));
                        foreach ($answers as $answer) {
                            SurveyManager::addAnswer($poll, $answer);
                        }

                        $this->output("Umfrage hinzugefügt");
                        break;
                    } else {
                        $this->output ("`#19 `bNicht genügend gültige Antworten definiert!`b`n");
                    }
                }

                $this->output("`bUmfragen Erstellen`b `n`n");
                $this->addForm("create")->head("createform", $this->url);
                $this->addSimpleTable("surveycreate")->head(500);

                // Question
                $this->getSimpleTable("surveycreate")->startHead();
                $this->output("Frage:");
                $this->getSimpleTable("surveycreate")->startData();
                $this->getForm("create")->inputText("question", ($question)?$question:"", 50, 255);
                $this->getSimpleTable("surveycreate")->closeRow();

                // Description
                $this->getSimpleTable("surveycreate")->startHead();
                $this->output("Beschreibung:");
                $this->getSimpleTable("surveycreate")->startData();
                $this->getForm("create")->inputText("description", ($description)?$description:"", 50, 255);
                $this->getSimpleTable("surveycreate")->closeRow();

                // Deadline
                $this->getSimpleTable("surveycreate")->startHead();
                $this->output("Ablaufdatum:");
                $this->getSimpleTable("surveycreate")->startData();
                $this->getForm("create")->inputText("deadline", ($deadline)?$deadline:date_format(date_create("+1 week"), "d.m.y"), 5, 8);
                $this->output("`bFormat:`b Tag.Monat.Jahr");
                $this->getSimpleTable("surveycreate")->closeRow();

                // Answer 1
                $this->getSimpleTable("surveycreate")->startRow();
                $this->getSimpleTable("surveycreate")->startHead();
                $this->output("Antwortmöglichkeit:");
                $this->getSimpleTable("surveycreate")->startData();
                $this->getForm("create")->inputText("answer[]", ($answers[0])?$answers[0]:"", 50, 255);
                $this->getSimpleTable("surveycreate")->closeRow();

                // Answer 2-n
                $this->getSimpleTable("surveycreate")->setCSS("answer");
                $this->getSimpleTable("surveycreate")->startRow();
                $this->getSimpleTable("surveycreate")->setCSS("");
                $this->getSimpleTable("surveycreate")->startHead();
                $this->output("Antwortmöglichkeit:");
                $this->getSimpleTable("surveycreate")->startData();
                $this->getForm("create")->inputText("answer[]", "", 50, 255);
                $this->getSimpleTable("surveycreate")->closeRow();

                $this->output(" <script>
                                    function addRow() {
                                        $('.answer').clone().insertAfter( $('.answer').removeClass('answer') );
                                    }
                                </script>
                             ", true);


                $this->getSimpleTable("surveycreate")->startData(false, 2);
                $this->output("<a onClick='addRow()'>Antwortmöglichkeit hinzufügen</a>", true);
                $this->getSimpleTable("surveycreate")->closeRow();

                // Question
                $this->getSimpleTable("surveycreate")->startData(false, 2);
                $this->output("`n");
                $this->getForm("create")->submitButton("Umfrage erstellen");
                $this->getSimpleTable("surveycreate")->closeRow();

                $this->getSimpleTable("surveycreate")->close();
                $this->getForm("create")->close();

                break;

            case "details":
                $poll = $em->find("Modules\Survey\Entities\Poll", $_GET['pollId']);

                $this->output("`bUmfragen Detail`b `n`n");
                $this->addSimpleTable("surveydetail")->head(500, 1);

                $this->getSimpleTable("surveydetail")->startHead();
                $this->output("Frage:");
                $this->getSimpleTable("surveydetail")->startData();
                $this->output($poll->question);
                $this->getSimpleTable("surveydetail")->closeRow();

                $this->getSimpleTable("surveydetail")->startHead();
                $this->output("Beschreibung:");
                $this->getSimpleTable("surveydetail")->startData();
                $this->output($poll->description);
                $this->getSimpleTable("surveydetail")->closeRow();

                $this->getSimpleTable("surveydetail")->startHead();
                $this->output("Erstellt von:");
                $this->getSimpleTable("surveydetail")->startData();
                $this->output($poll->creator->displayname);
                $this->getSimpleTable("surveydetail")->closeRow();

                $this->getSimpleTable("surveydetail")->startHead();
                $this->output("Erstelldatum:");
                $this->getSimpleTable("surveydetail")->startData();
                $this->output($poll->creationdate->format("d.m.y"));
                $this->getSimpleTable("surveydetail")->closeRow();

                $this->getSimpleTable("surveydetail")->startHead();
                $this->output("Ablaufdatum:");
                $this->getSimpleTable("surveydetail")->startData();
                $this->output($poll->deadline->format("d.m.y"));
                $this->getSimpleTable("surveydetail")->closeRow();

                $this->getSimpleTable("surveydetail")->startHead();
                $this->output("Antwortmöglichkeiten:");
                $this->getSimpleTable("surveydetail")->startData();
                foreach ($poll->answers as $answer) {
                    $this->output($answer->text . "(". $answer->votes->count()." Stimmen) . `n");
                }
                $this->getSimpleTable("surveydetail")->closeRow();

                $this->getSimpleTable("surveydetail")->startHead();
                $this->output("Voters:");
                $this->getSimpleTable("surveydetail")->startData();
                foreach ($poll->answers as $answer) {
                    foreach($answer->votes as $vote) {
                        $this->output($vote->voter->displayname. " (".$vote->votedate->format("d.m.y").")");
                    }
                }

                $this->getSimpleTable("surveydetail")->close();

                break;

            case "activate":
                $poll = $em->find("Modules\Survey\Entities\Poll", $_GET['pollId']);

                $poll->active = true;

                $this->output("Umfrage aktiviert");
                break;

            case "deactivate":
                $poll = $em->find("Modules\Survey\Entities\Poll", $_GET['pollId']);

                $poll->active = false;

                $this->output("Umfrage deaktiviert");
                break;

            case "delete":
                if (isset($_GET['force'])) {
                    SurveyManager::deletePoll($_GET['pollId']);

                    $this->output("Umfrage gelöscht");
                } else {
                    $this->output("Umfrage wirklich löschen?");

                    $this->nav->addTextLink("Ja, löschen", $this->url->base."/delete/?pollId=".$_GET['pollId']."&force=1");
                }
                break;
        }
    }

}