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
        $this->round                = 0;
        $this->active               = false;
        $this->battlemembersnapshot = "";
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

    public function removeMember(Character $character)
    {
        global $em;

        if ($member = $this->getMember($character)) {
            $this->members->remove($member);
        }
    }

    public function addMessage($message)
    {
        global $em;

        $newMessage          = new BattleMessage;
        $newMessage->battle  = $this;
        $newMessage->message = $message;

        $em->persist($newMessage);

        $this->messages->add($newMessage);
    }

    public function clearMessages()
    {
        $this->messages->clear();
    }

    public function addAction(Character $character, $target, Skill $skill)
    {
        global $em;

        if (!$this->actionDone($character)) {
            $newAction             = new BattleAction;
            $newAction->battle     = $this;
            $newAction->initiator  = $character;
            $newAction->target     = $target;
            $newAction->skill      = $skill;

            $em->persist($newAction);

            $this->actions->add($newAction);

            $this->setActionDone($character);
        }
    }

    public function setActionDone(Character $character)
    {
        $member = $this->getMember($character);
        $member->actiondone = true;
    }

    public function hasActionDone(Character $character)
    {
        foreach ($this->actions as $action) {
            if ($action->character === $character) {
                return true;
            }
        }

        return false;
    }

    public function getActionDoneList()
    {
        $result = array();

        foreach ($this->actions as $action) {
            $result[] = $actions->initiator;
        }

        return $result;
    }

    public function getActionNeededList()
    {
        $result = array_diff($this->getActionDoneList(), $this->members->toArray());

        return $result;
    }
}
?>