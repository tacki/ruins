<?php
/**
 * Battle Repository
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Repositories;
use Common\Controller\Error;
use Main\Entities\Battle;
use Main\Entities\BattleMember;
use Main\Entities\BattleMessage;
use Main\Entities\Character;
use Doctrine\DBAL\Types\Type;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Battle Repository
 * @package Ruins
 */
class BattleRepository extends Repository
{
    /**
     * Create a new Battle
     * @return Main\Entities\Battle
     */
    public function create()
    {
        $newBattle = new Battle;
        $newBattle->initiator = $initiator;

        $this->getEntityManager()->persist($newBattle);

        return $newBattle;
    }

    /**
     * Get Battle Member
     * @param Main\Entities\Character $character
     * @param Main\Entities\Battle $battle
     * @return Main\Entities\BattleMember
     */
    public function getBattleMember(Character $character, Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $repository = $this->getEntityManager()->getRepository("Main:BattleMember");

        $result = $repository->findOneBy(array("battle" => $battle, "character" => $character));

        return $result;
    }

    /**
     * Get all Members
     * @param Main\Entities\Battle $battle
     * @return Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMember
     */
    public function getAllMembers(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $result = new ArrayCollection;

        $repository = $this->getEntityManager()->getRepository("Main:BattleMember");

        foreach ($repository->findByBattle($battle) as $member) {
            $result->add($member);
        }

        return $result;
    }

    /**
     * Get all Fightactive Members
     * @param Main\Entities\Battle $battle
     * @return Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMember
     */
    public function getAllActiveMembers(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $result = new ArrayCollection;

        foreach ($this->getAllMembers($battle) as $member) {
            // Inactive Members are still Part of the Fight before they are excluded
            if ($member->isActive() || $member->isInactive()) {
                $result->add($member);
            }
        }

        return $result;
    }
    /**
     * Get all Attackers
     * @param Main\Entities\Battle $battle
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllAttackers(Battle $battle=NULL)
    {
        return $this->getAllMembersAtSide(BattleMember::SIDE_ATTACKERS, false, $battle);
    }

    /**
     * Get All Defenders
     * @param Main\Entities\Battle $battle
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllDefenders(Battle $battle=NULL)
    {
        return $this->getAllMembersAtSide(BattleMember::SIDE_DEFENDERS, false, $battle);
    }

    /**
     * Get all Members of a given Side
     * @param const $side
     * @param const $status
     * @param Main\Entities\Battle $battle
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllMembersAtSide($side, $status=false, Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $result = new ArrayCollection;

        foreach ($this->getAllMembers($battle) as $member) {
            if ($member->side === $side && ($status === false || $member->status === $status)) {
                $result->add($member);
            }
        }

        return $result;
    }

    /**
     * Get all Actions
     * @param Main\Entities\Battle $battle
     * @return Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllActions(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $result = new ArrayCollection;

        $repository = $this->getEntityManager()->getRepository("Main:BattleAction");

        foreach ($repository->findByBattle($battle) as $action) {
            $result->add($action);
        }

        return $result;
    }

    /**
     * Clear Actions
     * @param Main\Entities\Battle $battle
     */
    public function clearActions(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $actions = $this->getAllActions($battle);

        foreach ($actions as $action) {
            $this->getEntityManager()->remove($action);
        }
    }

    /**
     * Get List of Characters who made their Action
     * @param Main\Entities\Battle $battle
     * @return Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMember who used a Skill this round
     */
    public function getActionDoneList(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $result = new ArrayCollection;

        $actions = $this->getEntityManager()
                        ->getRepository("Main:BattleAction")
                        ->findByBattle($battle);

        foreach ($actions as $action) {
            $result->add($action->initiator);
        }

        return $result;
    }

    /**
     * Get List of Character who need to make their Action
     * @param Main\Entities\Battle $battle
     * @return Doctrine\Common\Collections\ArrayCollection
     */
    public function getActionNeededList(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $result = new ArrayCollection;

        $actionDoneList = $this->getActionDoneList();

        foreach ($this->getAllMembers($battle) as $member) {
            if (!$actionDoneList->contains($member)) {
                $result->add($member);
            }
        }

        return $result;
    }

    /**
     * Get Token Owner
     * @param Main\Entities\Battle $battle
     * @return Main\Entities\BattleMember
     */
    public function getTokenOwner(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $repository = $this->getEntityManager()->getRepository("Main:BattleMember");

        return $repository->findOneBy(array("battle" => $battle, "token" => true));
    }

    /**
     * Set the Battle token to the new Character
     * @param Main\Entities\Character $char Character Object
     * @param Main\Entities\Battle $battle
     */
    public function setTokenOwner(Character $character, Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        // Erase Token from old Owner
        $this->getTokenOwner($battle)->token = false;

        // Set new Token
        $this->getBattleMember($character)->token = true;
    }

    /**
     * Add a Character to a Battle
     * @param Main\Entities\Character $character
     * @param Main\Entities\Battle $battle
     * @return Main\Entities\BattleMember
     */
    public function addCharacterToBattle(Character $character, Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $repository = $this->getEntityManager()->getRepository("Main:BattleMember");

        if (!($member = $repository->findOneByCharacter($character))) {
            $member             = new BattleMember;
            $member->battle     = $battle;
            $member->character  = $character;
            $member->speed      = $character->getSpeed();

            $this->getEntityManager()->persist($member);
        }

        return $member;
    }

    /**
     * Remove a Character from a Battle he is in
     * @param Main\Entities\Character $character
     * @param Main\Entities\Battle $battle
     * @return boolean true if successful, else false
     */
    public function removeCharacterFromBattle(Character $character, Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        if ($member = $this->getBattleMember($character, $battle)) {
            $this->getEntityManager()->remove($member);
            $this->getEntityManager()->flush();
            return true;
        }

        return false;
    }

    /**
     * Return all Messages
     * @param Main\Entities\Battle $battle
     * @return Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMessage
     */
    public function getAllMessages(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $repository = $this->getEntityManager()->getRepository("Main:BattleMessage");

        return $repository->findByBattle($battle);
    }

    /**
     * Add a Message
     * @param string $message
     * @param Main\Entities\Battle $battle
     */
    public function addMessage($message, Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $newMessage = new BattleMessage;
        $newMessage->battle = $battle;
        $newMessage->message = $message;

        $this->getEntityManager()->persist($newMessage);
    }

    /**
     * Clear Battle Messages
     * @param Main\Entities\Battle $battle
     */
    public function clearMessages(Battle $battle=NULL)
    {
        // Fetch correct Battle Entity
        $battle = $this->getBattleOrReference($battle);

        $messages = $this->getAllMessages($battle);

        foreach ($messages as $message) {
            $this->getEntityManager()->remove($message);
        }
    }

    /**
     * Get all current Battles
     * @param bool $onlyactive Get only the active ones
     * @return array Array of Main\Entities\Battle with all corresponding Data
     */
    public function getList($onlyactive=false)
    {
        return $this->findByActive($onlyactive);
    }

    /**
     * Use given Object or earlier defined Reference
     * @param Main\Entities\Battle $battle
     * @throws Error
     * @return Main\Entities\Battle
     */
    protected function getBattleOrReference(Battle $battle=NULL)
    {
        if (!$battle && !($battle = $this->getReference())) {
            throw new Error("I need Information about a battle to operate!");
        }

        return $battle;
    }
}