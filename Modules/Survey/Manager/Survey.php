<?php
/**
 * Survey Manager
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Modules\Survey\Manager;
use DateTime;

/**
 * Survey Manager
 */
class Survey
{
    /**
     * Retrieve a specific Poll by ID
     * @param int $id
     * @return \Modules\Survey\Entities\Poll
     */
    public static function getPoll($id)
    {
        global $em;

        return $em->find("Modules\Survey\Entities\Poll", $id);
    }

    /**
    * Retrieve a specific Answer by ID
    * @param int $id
    * @return \Modules\Survey\Entities\Answer
    */
    public static function getAnswer($id)
    {
        global $em;

        return $em->find("Modules\Survey\Entities\Answer", $id);
    }

    /**
     * Retrieve all Polls
     * @param bool $active Only active Polls
     * @return array
     */
    public static function getAllPolls($active=true)
    {
        $qb = getQueryBuilder();

        $qb ->select("poll")
            ->from("Modules\Survey\Entities\Poll", "poll");
        if ($active) {
            $qb ->where("poll.deadline > ?1")
                ->andWhere("poll.creationdate < ?1")
                ->setParameter(1, new DateTime());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get defined Answers of a given Poll
     * @param \Modules\Survey\Entities\Poll $poll
     * @return array
     */
    public static function getAnswers(\Modules\Survey\Entities\Poll $poll)
    {
        $qb = getQueryBuilder();

        $qb ->select("answer")
            ->from("Modules\Survey\Entities\Answer", "answer")
            ->where("answer.poll = ?1")->setParameter(1, $poll);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all Votes for given Poll
     * @param \Modules\Survey\Entities\Poll $poll
     * @return array
     */
    public static function getAllVotes(\Modules\Survey\Entities\Poll $poll)
    {
        $qb = getQueryBuilder();

        $qb ->select("vote")
            ->from("Modules\Survey\Entities\Vote", "vote")
            ->where("vote.poll = ?1")->setParameter(1, $poll);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get Survey Result as an array
     * @param \Modules\Survey\Entities\Poll $poll
     * @return array 1-dimensional array with $answer->text => # of Votes
     */
    public static function getSurveyResult(\Modules\Survey\Entities\Poll $poll)
    {
        global $em;

        $result = array();
        foreach ($poll->answers as $answer) {
            $result[$answer->text] = self::getNrOfVotes($answer);
        }

        return $result;
    }

    /**
     * Get Number of Votes for a given Answer
     * @param \Modules\Survey\Entities\Answer $answer
     * @return int Number of Votes
     */
    public static function getNrOfVotes(\Modules\Survey\Entities\Answer $answer)
    {
        $qb = getQueryBuilder();

        $qb ->select("COUNT(vote)")
            ->from("Modules\Survey\Entities\Vote", "vote")
            ->where("vote.answer = ?1")->setParameter(1, $answer);

        $result = $qb->getQuery()->getOneOrNullResult();

        if(is_array($result)) $result = array_shift($result);

        return (int)$result;
    }

    /**
     * Create a new Poll
     * @param string $question The Question to ask
     * @param DateTime $deadline
     * @return \Modules\Survey\Entities\Poll
     */
    public static function addPoll($question, DateTime $deadline)
    {
        global $em, $user;

        $poll = new \Modules\Survey\Entities\Poll;
        $poll->question = $question;
        $poll->deadline = $deadline;

        if ($user->character instanceof \Main\Entities\Character) $poll->creator = $user->character;

        $em->persist($poll);

        return $poll;
    }

    /**
     * Add an Answer to a given Poll
     * @param \Modules\Survey\Entities\Poll $poll
     * @param string $answer
     */
    public static function addAnswer(\Modules\Survey\Entities\Poll $poll, $answer)
    {
        $poll->answers->add($answer);
    }

    /**
     * Check if a character has already voted
     * @param \Main\Entities\Character $character
     * @param \Modules\Survey\Entities\Poll $poll
     * @return int|flase Answer-ID or false
     */
    public static function hasVoted(\Main\Entities\Character $character, \Modules\Survey\Entities\Poll $poll)
    {
        $qb = getQueryBuilder();

        $qb ->select("vote")
            ->from("Modules\Survey\Entities\Vote", "vote")
            ->where("vote.poll = ?1")->setParameter(1, $poll)
            ->andWhere("vote.voter = ?2")->setParameter(2, $character);

        if ($result = $qb->getQuery()->getOneOrNullResult()) {
            return $result->answer->id;
        } else {
            return false;
        }
    }

    /**
     * Vote for an Answer
     * @param integer $pollId
     * @param integer $answerId
     */
    public static function vote($pollId, $answerId)
    {
        global $em, $user;

        if (self::hasVoted($user->character, self::getSurvey($pollId))) {
            return false;
        }

        $vote = new \Modules\Survey\Entities\Vote;
        $vote->voter  = $user->character;
        $vote->poll   = self::getSurvey($pollId);
        $vote->answer = self::getAnswer($answerId);

        $em->persist($vote);
        $em->flush();
    }
}
?>