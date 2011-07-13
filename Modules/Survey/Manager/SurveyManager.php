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
use Doctrine\DBAL\Types\Type;
use Main\Entities\Character;
use Modules\Survey\Entities\Poll;
use Modules\Survey\Entities\Vote;
use Modules\Survey\Entities\Answer;
use Common\Controller\Registry;

/**
 * Survey Manager
 */
class SurveyManager
{
    /**
     * Retrieve a specific Poll by ID
     * @param int $id
     * @return \Modules\Survey\Entities\Poll
     */
    public static function getPoll($id)
    {
        $em = Registry::getEntityManager();

        return $em->find("Modules\Survey\Entities\Poll", $id);
    }

    /**
    * Retrieve a specific Answer by ID
    * @param int $id
    * @return \Modules\Survey\Entities\Answer
    */
    public static function getAnswer($id)
    {
        $em = Registry::getEntityManager();

        return $em->find("Modules\Survey\Entities\Answer", $id);
    }

    /**
     * Retrieve all Polls
     * @param bool $active Only active Polls
     * @return array
     */
    public static function getAllPolls($active=true)
    {
        $em = Registry::getEntityManager();

        $qb = $em->createQueryBuilder();

        $qb ->select("poll")
            ->from("Modules\Survey\Entities\Poll", "poll");
        if ($active) {
            $qb ->where("poll.deadline >= ?1")
                ->andWhere("poll.creationdate < ?1")
                ->andWhere("poll.active = ?2")
                ->setParameter(1, new DateTime())
                ->setParameter(2, true, Type::BOOLEAN);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get defined Answers of a given Poll
     * @param \Modules\Survey\Entities\Poll $poll
     * @return array
     */
    public static function getAnswers(Poll $poll)
    {
        return $poll->answers;
    }

    public static function getNrOfAnswers(Poll $poll)
    {
        return self::getAnswers($poll)->count();
    }

    /**
     * Get all Votes for given Poll
     * @param \Modules\Survey\Entities\Poll $poll
     * @return array
     */
    public static function getAllVotes(Poll $poll)
    {
        $result = array();
        foreach ($poll->answers as $answer) {
            $result[$answer->id] = $answer->votes;
        }

        return $result;
    }

    /**
     * Get Total Number of Votes for a poll
     * @param \Modules\Survey\Entities\Poll $poll
     * @return int # of Votes
     */
    public static function getTotalNrOfVotes(Poll $poll)
    {
        $result = 0;
        foreach ($poll->answers as $answer) {
            $result += $answer->votes->count();
        }

        return $result;
    }

    /**
     * Get Survey Result as an array
     * @param \Modules\Survey\Entities\Poll $poll
     * @return array 1-dimensional array with $answer->text => # of Votes
     */
    public static function getSurveyResult(Poll $poll)
    {
        $result = array();
        foreach ($poll->answers as $answer) {
            $result[$answer->text] = $answer->count();
        }

        return $result;
    }

    /**
     * Get Number of Votes for a given Answer
     * @param \Modules\Survey\Entities\Answer $answer
     * @return int Number of Votes
     */
    public static function getNrOfVotes(Answer $answer)
    {
        return $answer->count();
    }

    /**
     * Create a new Poll
     * @param string $question The Question to ask
     * @param DateTime $deadline
     * @return \Modules\Survey\Entities\Poll
     */
    public static function addPoll($question, $description, DateTime $deadline)
    {
        $em = Registry::getEntityManager();
        $user = Registry::getUser();

        $poll = new Poll;
        $poll->question = $question;
        $poll->description = $description;
        $poll->deadline = $deadline;

        if ($user->character instanceof Character) $poll->creator = $user->character;

        $em->persist($poll);

        return $poll;
    }

    /**
    * Delete a Poll and all of its Answers and Votes
    * @param integer $pollId
    */
    public static function deletePoll($pollId)
    {
        $em = Registry::getEntityManager();

        $poll = $em->find("Modules\Survey\Entities\Poll", $pollId);

        if ($poll) {
            // Remove is cascaded to Answers and to Votes
            $em->remove($poll);
        }
    }

    /**
     * Add an Answer to a given Poll
     * @param \Modules\Survey\Entities\Poll $poll
     * @param string $answer
     */
    public static function addAnswer(Poll $poll, $answer)
    {
        $em = Registry::getEntityManager();

        $answerObject = new Answer();
        $answerObject->poll = $poll;
        $answerObject->text = $answer;

        $em->persist($answerObject);

        $poll->answers->add($answerObject);
    }

    /**
    * Remove an Answer from a given Poll
    * @param \Modules\Survey\Entities\Poll $poll
    * @param string $answer
    */
    public static function removeAnswer(Poll $poll, $answer)
    {
        $poll->answers->removeElement($answer);
    }

    /**
     * Check if a character has already voted
     * @param \Main\Entities\Character $character
     * @param \Modules\Survey\Entities\Poll $poll
     * @return Modules\Survey\Entities\Answer Answer he voted for
     */
    public static function hasVoted(Character $character, Poll $poll)
    {
        $em = Registry::getEntityManager();

        $qb = $em->createQueryBuilder();

        $qb ->select("vote")
            ->from("Modules\Survey\Entities\Vote", "vote")
            ->where("vote.poll = ?1")->setParameter(1, $poll)
            ->andWhere("vote.voter = ?2")->setParameter(2, $character);

        if ($result = $qb->getQuery()->getOneOrNullResult()) {
            return $result->answer;
        } else {
            return NULL;
        }
    }

    /**
     * Vote for an Answer
     * @param integer $pollId
     * @param integer $answerId
     */
    public static function vote($pollId, $answerId)
    {
        $em = Registry::getEntityManager();
        $user = Registry::getUser();

        if (self::hasVoted($user->character, self::getPoll($pollId))) {
            return false;
        }

        $vote = new Vote;
        $vote->voter  = $user->character;
        $vote->poll   = self::getPoll($pollId);
        $vote->answer = self::getAnswer($answerId);

        $em->persist($vote);
        $em->flush();
    }

    /**
     * We are Dictator, we can remove votes
     * @param integer $voteID
     */
    public static function removeVote($voteID)
    {
        $em = Registry::getEntityManager();

        $result = $em->find("Modules\Survey\Entities\Vote", "vote", $voteID);

        if ($result) {
            $em->remove($result);
        }
    }
}
?>