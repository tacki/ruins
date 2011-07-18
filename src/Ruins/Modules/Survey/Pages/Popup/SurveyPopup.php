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

class SurveyPopup extends Popup
{
    protected $pagetitle  = "Survey Module";

    public function setTitle()
    {
        $this->set("pagetitle", $this->pagetitle);
        $this->set("headtitle", $this->pagetitle);
    }

    public function createMenu()
    {
        $this->nav->addLink("Umfrage", $this->url);
    }

    public function createContent(array $parameters)
    {
        $this->addCSS("Survey.css");

        $polls = SurveyManager::getAllPolls();

        if (!count($polls)) {
            $this->output("Zur Zeit gibt es keine aktiven Umfragen");
        }

        if (isset($_GET['poll_id']) && isset($_POST['chooser'])) {
            SurveyManager::vote($_GET['poll_id'], $_POST['chooser']);
        }

        foreach ($polls as $poll) {
            $hasVoted = SurveyManager::hasVoted($user->character, $poll);

            $this->addSimpleTable("survey")->setCSS("survey");
            $this->addForm("survey")->setCSS("survey");

            $this->getForm("survey")->head("survey", $this->url->setParameter("poll_id", $poll->id));
            $this->getSimpleTable("survey")->head(400);

            // Question
            $this->getSimpleTable("survey")->startRow();
            $this->getSimpleTable("survey")->startHead(false, 2);
            $this->output($poll->question);
            $this->getSimpleTable("survey")->closeRow();

            // Description
            $this->getSimpleTable("survey")->startRow();
            $this->getSimpleTable("survey")->startData(false, 2);
            $this->output($poll->description."`n");
            $this->getSimpleTable("survey")->closeRow();


            foreach ($poll->answers as $answer) {
                $this->getSimpleTable("survey")->startRow();
                $this->getSimpleTable("survey")->startData();
                $this->output($answer->text);
                $this->getSimpleTable("survey")->startData(20);
                if ($hasVoted == $answer) {
                    $this->getForm("survey")->radio("chooser", $answer->id, true, true);
                } elseif ($hasVoted) {
                    $this->getForm("survey")->radio("chooser", $answer->id, false, true);
                } else {
                    $this->getForm("survey")->radio("chooser", $answer->id);
                }
                $this->getSimpleTable("survey")->closeRow();
            }

            $this->getSimpleTable("survey")->close();
            $this->closeSimpleTable("survey");

            // Submit
            if ($hasVoted) {
                $this->output("Du hast bereits abgestimmt!");
            } else {
                $this->getForm("survey")->setCSS("submit");
                $this->getForm("survey")->submitButton("Absenden");
            }
            $this->getForm("survey")->close();
            $this->closeForm("survey");


            $this->output("`s`c".SurveyManager::getTotalNrOfVotes($poll)." Stimme(n) insgesamt.`c`s");

            $this->output("`n`n");
        }
    }
}
