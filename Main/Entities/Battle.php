<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="battles")
 */
class Battle extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Main\Entities\Character
     */
    protected $initiator;

    /**
    * @OneToMany(targetEntity="BattleAction", mappedBy="battle", cascade={"all"})
    * @var \Doctrine\Common\Collections\ArrayCollection
    */
    protected $actions;

    /**
     * @OneToMany(targetEntity="BattleMember", mappedBy="battle", cascade={"all"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $members;

    /**
     * @OneToMany(targetEntity="BattleMessage", mappedBy="battle", cascade={"all"})
     * @OrderBy({"date" = "desc"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $messages;

    /**
     * @OneToOne(targetEntity="Timer", cascade={"all"})
     * @var Main\Entities\Timer
     */
    protected $timer;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $round;

    /**
     * @Column(type="boolean")
     * @var bool
     */
    protected $active;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $battlemembersnapshot;

    public function __construct()
    {
        // Default Values
        $this->actions              = new \Doctrine\Common\Collections\ArrayCollection;
        $this->members              = new \Doctrine\Common\Collections\ArrayCollection;
        $this->messages             = new \Doctrine\Common\Collections\ArrayCollection;
        $this->timer                = new \Main\Entities\Timer(uniqid("battle_"));
        $this->round                = 0;
        $this->active               = false;
        $this->battlemembersnapshot = "";
    }

    /**
     * Check if the Battle is active
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
    * Check if a Character is a Member of this Battle
    * @param Main\Entities\Character $char Character to check
    * @return bool true if the char is a Member, else false
    */
    public function isMember(Character $character)
    {
        foreach ($this->members as $member) {
            if ($member->character === $character) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Memberentry for a given Character
     * @param Main\Entities\Character $character
     * @return Main\Entities\BattleMember|false
     */
    public function getMember(Character $character)
    {
        foreach ($this->members as $member) {
            if ($member->character === $character) {
                return $member;
            }
        }

        return false;
    }

    /**
     * Get all Members
     * @return Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMember
     */
    public function getAllMembers()
    {
        return $this->members;
    }

    /**
     * Get all Attackers
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllAttackers()
    {
        return $this->getMembersAtSide(\Main\Entities\BattleMember::SIDE_ATTACKERS);
    }

    /**
     * Get All Defenders
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllDefenders()
    {
        return $this->getMembersAtSide(\Main\Entities\BattleMember::SIDE_DEFENDERS);
    }

    /**
     * Get all Fightactive Members
     * @return Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMember
     */
    public function getAllActiveMembers()
    {
        $result = new \Doctrine\Common\Collections\ArrayCollection;

        foreach ($this->members as $member) {
            // Inactive Members are still Part of the Fight before they are excluded
            if ($member->isActive() || $member->isInactive()) {
                $result->add($member);
            }
        }

        return $result;
    }

    /**
     * Get all Members of a given Side
     * @param const $side
     * @param const $status
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMembersAtSide($side, $status=false)
    {
        $result = new \Doctrine\Common\Collections\ArrayCollection;

        foreach ($this->members as $member) {
            if ($member->side === $side && ($status === false || $member->status === $status)) {
                $result->add($member);
            }
        }

        return $result;
    }

    /**
     * Add a new Member to the Battle
     * @param Character $character
     * @param string $side The side the Character joins (attacker, defender, ...)
     */
    public function addMember(Character $character, $side)
    {
        global $em;

        if (!$this->isMember($character)) {
            $newMember             = new BattleMember;
            $newMember->battle     = $this;
            $newMember->character  = $character;
            $newMember->side       = $side;
            $newMember->speed      = $character->getSpeed();

            $em->persist($newMember);

            $this->members->add($newMember);
        }
    }

    /**
     * Remove a Member from the Battle
     * @param Character $character The Character to remove
     */
    public function removeMember(Character $character)
    {
        global $em;

        if ($member = $this->getMember($character)) {
            $em->remove($member);
        }
    }

    /**
     * Set Status of a BattleMember
     * @param Character $character Character Object
     * @param int $status
     */
    public function setMemberStatus(Character $character, $status)
    {
        $this->getMember($character)->status = $status;
    }

    /**
    * Set Member Side
    * @param Character $char Character Object
    * @param int $status New side (attacker, defender, neutral)
    */
    public function setMemberSide(Character $character, $side)
    {
        $this->getMember($character)->side = $side;
    }

    /**
    * Get Token Owner
    * @return Main\Entities\BattleMember
    */
    public function getTokenOwner()
    {
        foreach ($this->members as $member) {
            if ($member->token == true) {
                return $member;
            }
        }
    }

    /**
    * Set the Battle token to the new Character
    * @param Character $char Character Object
    */
    public function setTokenOwner(Character $character)
    {
        // Erase Token from old Owner
        $this->getTokenOwner()->token = false;

        // Set new Token
        $this->getMember($character)->token = true;
    }

    /**
     * Add a single Battle Message to the Database
     * @param string $message
     */
    public function addMessage($message)
    {
        global $em;

        $newMessage          = new BattleMessage;
        $newMessage->battle  = $this;
        $newMessage->message = $message;

        $em->persist($newMessage);

    }

    /**
     * Return all Messages
     * @return Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMessage
     */
    public function getAllMessages()
    {
        return $this->messages;
    }

    /**
     * Truncate Messages
     */
    public function clearMessages()
    {
        $this->messages->clear();
    }

    /**
    * Get List of Characters who made their Action
    * @return Doctrine\Common\Collections\ArrayCollection Array of Main\Entities\BattleMember who used a Skill this round
    */
    public function getActionDoneList()
    {
        $result = new \Doctrine\Common\Collections\ArrayCollection;

        foreach ($this->actions as $action) {
            $result->add($action->initiator);
        }

        return $result;
    }

    /**
     * Get List of Character who need to make their Action
     * @return Doctrine\Common\Collections\ArrayCollection
     */
    public function getActionNeededList()
    {
        $result = new \Doctrine\Common\Collections\ArrayCollection;
        $actionDoneList = $this->getActionDoneList();

        foreach ($this->members as $member) {
            if (!$actionDoneList->contains($member)) {
                $result->add($member);
            }
        }

        return $result;
    }
}
?>