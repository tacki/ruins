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
use Modules\Survey\Manager;

/**
 * Page Content
 */
$page->set("pagetitle", "Module");
$page->set("headtitle", "Module");

$page->nav->addHead("Umfragen")
          ->addLink("Übersicht", $page->url->base)
          ->addLink("Erstellen", $page->url->base."&op=create")
          ->addHead("Navigation")
          ->addLink("Zurück zur Administration", "page=admin/main");

switch ($_GET['op']) {

    default:
        $polls = Manager\Survey::getAllPolls(false);

        $page->output("`bUmfragen Liste`b `n`n");

        $page->addSimpleTable("surveylist")->head("100%", 1);

        // Header
        $page->getSimpleTable("surveylist")->startHead();
        $page->output("Id");
        $page->getSimpleTable("surveylist")->startHead();
        $page->output("Ersteller");
        $page->getSimpleTable("surveylist")->startHead();
        $page->output("Frage");
        $page->getSimpleTable("surveylist")->startHead();
        $page->output("Ablaufdatum");
        $page->getSimpleTable("surveylist")->startHead();
        $page->output("# Stimmen");
        $page->getSimpleTable("surveylist")->startHead();
        $page->output("Aktion");
        $page->getSimpleTable("surveylist")->closeRow();

        foreach ($polls as $poll) {
            // Id
            $page->getSimpleTable("surveylist")->startData(10);
            $page->output("`c".$poll->id."`c");

            // Creator
            $page->getSimpleTable("surveylist")->startData(100);
            $page->output("`c".$poll->creator->displayname."`c");

            // Question and Description
            $page->getSimpleTable("surveylist")->startData();
            $page->output($poll->question. "`n" . "`s".$poll->description."`s");

            // Deadline
            $page->getSimpleTable("surveylist")->startData(10);
            $page->output("`c".$poll->deadline->format("d.m.y")."`c");

            // # of Votes
            $page->getSimpleTable("surveylist")->startData(10);
            $page->output("`c".Manager\Survey::getTotalNrOfVotes($poll)."`c");

            // Action
            $page->getSimpleTable("surveylist")->startData(50);
            $page->nav->addTextLink("Details", $page->url->base."&op=details&pollId=$poll->id");
            if ($poll->active) {
                $page->nav->addTextLink("Deaktivieren", $page->url->base."&op=deactivate&pollId=$poll->id");
            } else {
                $page->nav->addTextLink("Aktivieren", $page->url->base."&op=activate&pollId=$poll->id");
            }
            $page->nav->addTextLink("Löschen", $page->url->base."&op=delete&pollId=$poll->id");

            $page->getSimpleTable("surveylist")->closeRow();
        }
        $page->getSimpleTable("surveylist")->close();
        break;

    case "create":
        if (isset($_POST['question'])) {
            $question     = $_POST['question'];
            $description  = $_POST['description'];
            $deadline     = $_POST['deadline'];
            $answers = array_filter($_POST['answer']);


            if (count($answers) > 1) {
                $poll = Manager\Survey::addPoll($question, $description, new DateTime($deadline));
                foreach ($answers as $answer) {
                    Manager\Survey::addAnswer($poll, $answer);
                }

                $page->output("Umfrage hinzugefügt");
                break;
            } else {
                $page->output ("`#19 `bNicht genügend gültige Antworten definiert!`b`n");
            }
        }

        $page->output("`bUmfragen Erstellen`b `n`n");
        $page->addForm("create")->head("createform", $page->url);
        $page->addSimpleTable("surveycreate")->head(500);

        // Question
        $page->getSimpleTable("surveycreate")->startHead();
        $page->output("Frage:");
        $page->getSimpleTable("surveycreate")->startData();
        $page->getForm("create")->inputText("question", ($question)?$question:"", 50, 255);
        $page->getSimpleTable("surveycreate")->closeRow();

        // Description
        $page->getSimpleTable("surveycreate")->startHead();
        $page->output("Beschreibung:");
        $page->getSimpleTable("surveycreate")->startData();
        $page->getForm("create")->inputText("description", ($description)?$description:"", 50, 255);
        $page->getSimpleTable("surveycreate")->closeRow();

        // Deadline
        $page->getSimpleTable("surveycreate")->startHead();
        $page->output("Ablaufdatum:");
        $page->getSimpleTable("surveycreate")->startData();
        $page->getForm("create")->inputText("deadline", ($deadline)?$deadline:date_format(date_create("+1 week"), "d.m.y"), 5, 8);
        $page->output("`bFormat:`b Tag.Monat.Jahr");
        $page->getSimpleTable("surveycreate")->closeRow();

        // Answer 1
        $page->getSimpleTable("surveycreate")->startRow();
        $page->getSimpleTable("surveycreate")->startHead();
        $page->output("Antwortmöglichkeit:");
        $page->getSimpleTable("surveycreate")->startData();
        $page->getForm("create")->inputText("answer[]", ($answers[0])?$answers[0]:"", 50, 255);
        $page->getSimpleTable("surveycreate")->closeRow();

        // Answer 2-n
        $page->getSimpleTable("surveycreate")->setCSS("answer");
        $page->getSimpleTable("surveycreate")->startRow();
        $page->getSimpleTable("surveycreate")->setCSS("");
        $page->getSimpleTable("surveycreate")->startHead();
        $page->output("Antwortmöglichkeit:");
        $page->getSimpleTable("surveycreate")->startData();
        $page->getForm("create")->inputText("answer[]", "", 50, 255);
        $page->getSimpleTable("surveycreate")->closeRow();

        $page->output(" <script>
                            function addRow() {
                                $('.answer').clone().insertAfter( $('.answer').removeClass('answer') );
                            }
                        </script>
                     ", true);


        $page->getSimpleTable("surveycreate")->startData(false, 2);
        $page->output("<a onClick='addRow()'>Antwortmöglichkeit hinzufügen</a>", true);
        $page->getSimpleTable("surveycreate")->closeRow();

        // Question
        $page->getSimpleTable("surveycreate")->startData(false, 2);
        $page->output("`n");
        $page->getForm("create")->submitButton("Umfrage erstellen");
        $page->getSimpleTable("surveycreate")->closeRow();

        $page->getSimpleTable("surveycreate")->close();
        $page->getForm("create")->close();

        break;

    case "details":
        global $em;

        $poll = $em->find("Modules\Survey\Entities\Poll", $_GET['pollId']);

        $page->output("`bUmfragen Detail`b `n`n");
        $page->addSimpleTable("surveydetail")->head(500, 1);

        $page->getSimpleTable("surveydetail")->startHead();
        $page->output("Frage:");
        $page->getSimpleTable("surveydetail")->startData();
        $page->output($poll->question);
        $page->getSimpleTable("surveydetail")->closeRow();

        $page->getSimpleTable("surveydetail")->startHead();
        $page->output("Beschreibung:");
        $page->getSimpleTable("surveydetail")->startData();
        $page->output($poll->description);
        $page->getSimpleTable("surveydetail")->closeRow();

        $page->getSimpleTable("surveydetail")->startHead();
        $page->output("Erstellt von:");
        $page->getSimpleTable("surveydetail")->startData();
        $page->output($poll->creator->displayname);
        $page->getSimpleTable("surveydetail")->closeRow();

        $page->getSimpleTable("surveydetail")->startHead();
        $page->output("Erstelldatum:");
        $page->getSimpleTable("surveydetail")->startData();
        $page->output($poll->creationdate->format("d.m.y"));
        $page->getSimpleTable("surveydetail")->closeRow();

        $page->getSimpleTable("surveydetail")->startHead();
        $page->output("Ablaufdatum:");
        $page->getSimpleTable("surveydetail")->startData();
        $page->output($poll->deadline->format("d.m.y"));
        $page->getSimpleTable("surveydetail")->closeRow();

        $page->getSimpleTable("surveydetail")->startHead();
        $page->output("Antwortmöglichkeiten:");
        $page->getSimpleTable("surveydetail")->startData();
        foreach ($poll->answers as $answer) {
            $page->output($answer->text . "(". $answer->votes->count()." Stimmen) . `n");
        }
        $page->getSimpleTable("surveydetail")->closeRow();

        $page->getSimpleTable("surveydetail")->startHead();
        $page->output("Voters:");
        $page->getSimpleTable("surveydetail")->startData();
        foreach ($poll->answers as $answer) {
            foreach($answer->votes as $vote) {
                $page->output($vote->voter->displayname. " (".$vote->votedate->format("d.m.y").")");
            }
        }

        $page->getSimpleTable("surveydetail")->close();

        break;

    case "activate":
        global $em;

        $poll = $em->find("Modules\Survey\Entities\Poll", $_GET['pollId']);

        $poll->active = true;

        $page->output("Umfrage aktiviert");
        break;

    case "deactivate":
        global $em;

        $poll = $em->find("Modules\Survey\Entities\Poll", $_GET['pollId']);

        $poll->active = false;

        $page->output("Umfrage deaktiviert");
        break;

    case "delete":
        if (isset($_GET['force'])) {
            Manager\Survey::deletePoll($_GET['pollId']);

            $page->output("Umfrage gelöscht");
        } else {
            $page->output("Umfrage wirklich löschen?");

            $page->nav->addTextLink("Ja, löschen", $page->url->base."&op=delete&force=1&pollId=".$_GET['pollId']);
        }
        break;
}
?>