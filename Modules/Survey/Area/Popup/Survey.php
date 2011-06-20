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


/**
 * Page Content
 */
$popup->set("pagetitle", "Supportanfrage");
$popup->set("headtitle", "Supportanfrage");

$popup->nav->addLink("Umfrage", $popup->url);

$surveys = \Modules\Survey\Manager\Survey::getAllPolls(false);

if (isset($_GET['poll_id']) && isset($_POST['chooser'])) {
    \Modules\Survey\Manager\Survey::vote($_GET['poll_id'], $_POST['chooser']);
}

foreach ($surveys as $survey) {
    $hasVoted = \Modules\Survey\Manager\Survey::hasVoted($user->character, $survey);

    $popup->addSimpleTable("survey");
    $popup->addForm("survey");

    $popup->getForm("survey")->head("survey", $popup->url->setParameter("poll_id", $survey->id));
    $popup->getSimpleTable("survey")->head(400, 1, 1);

    // Question
    $popup->getSimpleTable("survey")->startRow();
    $popup->getSimpleTable("survey")->startData(false, 2);
    $popup->output("`c`b".$survey->question."`b`c");
    $popup->getSimpleTable("survey")->closeRow();

    foreach ($survey->answers as $answer) {
        $popup->getSimpleTable("survey")->startRow();
        $popup->getSimpleTable("survey")->startData();
        $popup->output($answer->text);
        $popup->getSimpleTable("survey")->startData(20);
        if ($hasVoted == $answer->id) {
            $popup->getForm("survey")->radio("chooser", $answer->id, true, true);
        } elseif ($hasVoted !== false) {
            $popup->getForm("survey")->radio("chooser", $answer->id, false, true);
        } else {
            $popup->getForm("survey")->radio("chooser", $answer->id);
        }
        $popup->getSimpleTable("survey")->closeRow();
    }

    // Submit
    if ($hasVoted !== false) {
        $popup->getSimpleTable("survey")->startRow();
        $popup->getSimpleTable("survey")->startData(false, 2);
        $popup->output("Du hast bereits abgestimmt!");
        $popup->getSimpleTable("survey")->closeRow();

        $popup->getSimpleTable("survey")->close();
        $popup->getForm("survey")->close();
    } else {
        $popup->getSimpleTable("survey")->startRow();
        $popup->getSimpleTable("survey")->startData(false, 2);
        $popup->getForm("survey")->submitButton("Absenden");
        $popup->getSimpleTable("survey")->closeRow();

        $popup->getSimpleTable("survey")->close();
        $popup->getForm("survey")->close();
    }
}
?>