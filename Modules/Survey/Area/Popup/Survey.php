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
use Modules\Survey\Manager;

/**
 * Page Content
 */
$popup->addCSS("Survey.css");

$popup->set("pagetitle", "Supportanfrage");
$popup->set("headtitle", "Supportanfrage");

$popup->nav->addLink("Umfrage", $popup->url);

$polls = Manager\Survey::getAllPolls();

if (!count($polls)) {
    $popup->output("Zur Zeit gibt es keine aktiven Umfragen");
}

if (isset($_GET['poll_id']) && isset($_POST['chooser'])) {
    Manager\Survey::vote($_GET['poll_id'], $_POST['chooser']);
}

foreach ($polls as $poll) {
    $hasVoted = Manager\Survey::hasVoted($user->character, $poll);

    $popup->addSimpleTable("survey")->setCSS("survey");
    $popup->addForm("survey")->setCSS("survey");

    $popup->getForm("survey")->head("survey", $popup->url->setParameter("poll_id", $poll->id));
    $popup->getSimpleTable("survey")->head(400);

    // Question
    $popup->getSimpleTable("survey")->startRow();
    $popup->getSimpleTable("survey")->startHead(false, 2);
    $popup->output($poll->question);
    $popup->getSimpleTable("survey")->closeRow();

    // Description
    $popup->getSimpleTable("survey")->startRow();
    $popup->getSimpleTable("survey")->startData(false, 2);
    $popup->output($poll->description."`n");
    $popup->getSimpleTable("survey")->closeRow();


    foreach ($poll->answers as $answer) {
        $popup->getSimpleTable("survey")->startRow();
        $popup->getSimpleTable("survey")->startData();
        $popup->output($answer->text);
        $popup->getSimpleTable("survey")->startData(20);
        if ($hasVoted == $answer) {
            $popup->getForm("survey")->radio("chooser", $answer->id, true, true);
        } elseif ($hasVoted) {
            $popup->getForm("survey")->radio("chooser", $answer->id, false, true);
        } else {
            $popup->getForm("survey")->radio("chooser", $answer->id);
        }
        $popup->getSimpleTable("survey")->closeRow();
    }

    $popup->getSimpleTable("survey")->close();
    $popup->closeSimpleTable("survey");

    // Submit
    if ($hasVoted) {
        $popup->output("Du hast bereits abgestimmt!");
    } else {
        $popup->getForm("survey")->setCSS("submit");
        $popup->getForm("survey")->submitButton("Absenden");
    }
    $popup->getForm("survey")->close();
    $popup->closeForm("survey");


    $popup->output("`s`c".Manager\Survey::getTotalNrOfVotes($poll)." Stimme(n) insgesamt.`c`s");

    $popup->output("`n`n");
}
?>