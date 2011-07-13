<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity(repositoryClass="Main\Repositories\BattleRepository")
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
        $this->round                = 1;
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
}
?>