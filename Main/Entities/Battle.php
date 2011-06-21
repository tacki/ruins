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
     * @ManyToOne(targetEntity="Timer")
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
        $this->round                = 0;
        $this->active               = false;
        $this->battlemembersnapshot = "";
    }
}
?>