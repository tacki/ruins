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
namespace Ruins\Modules\Survey\Pages\Popup;
use Ruins\Modules\Survey\Manager\SurveyManager;
use Ruins\Common\Controller\AbstractPageObject;

class SurveyPopup extends AbstractPageObject
{
    public $title  = "Umfrage";

    public function createContent($page, $parameters)
    {
        $page->addCSS("Survey.css");

        $polls = SurveyManager::getAllPolls();

        if (!count($polls)) {
            $page->output("Zur Zeit gibt es keine aktiven Umfragen");
        }

        if (isset($_GET['poll_id']) && isset($_POST['chooser'])) {
            SurveyManager::vote($_GET['poll_id'], $_POST['chooser']);
        }

        foreach ($polls as $poll) {
            $hasVoted = SurveyManager::hasVoted($user->character, $poll);

            $page->addSimpleTable("survey")->setCSS("survey");
            $page->addForm("survey")->setCSS("survey");

            $page->getForm("survey")->head("survey", $page->url->setParameter("poll_id", $poll->id));
            $page->getSimpleTable("survey")->head(400);

            // Question
            $page->getSimpleTable("survey")->startRow();
            $page->getSimpleTable("survey")->startHead(false, 2);
            $page->output($poll->question);
            $page->getSimpleTable("survey")->closeRow();

            // Description
            $page->getSimpleTable("survey")->startRow();
            $page->getSimpleTable("survey")->startData(false, 2);
            $page->output($poll->description."`n");
            $page->getSimpleTable("survey")->closeRow();


            foreach ($poll->answers as $answer) {
                $page->getSimpleTable("survey")->startRow();
                $page->getSimpleTable("survey")->startData();
                $page->output($answer->text);
                $page->getSimpleTable("survey")->startData(20);
                if ($hasVoted == $answer) {
                    $page->getForm("survey")->radio("chooser", $answer->id, true, true);
                } elseif ($hasVoted) {
                    $page->getForm("survey")->radio("chooser", $answer->id, false, true);
                } else {
                    $page->getForm("survey")->radio("chooser", $answer->id);
                }
                $page->getSimpleTable("survey")->closeRow();
            }

            $page->getSimpleTable("survey")->close();
            $page->closeSimpleTable("survey");

            // Submit
            if ($hasVoted) {
                $page->output("Du hast bereits abgestimmt!");
            } else {
                $page->getForm("survey")->setCSS("submit");
                $page->getForm("survey")->submitButton("Absenden");
            }
            $page->getForm("survey")->close();
            $page->closeForm("survey");


            $page->output("`s`c".SurveyManager::getTotalNrOfVotes($poll)." Stimme(n) insgesamt.`c`s");

            $page->output("`n`n");
        }
    }
}
