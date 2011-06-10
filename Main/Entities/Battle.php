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
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $initiator;

    /**
     * @ManyToOne(targetEntity="Timer")
     */
    protected $timer;

    /** @Column(type="integer") */
    protected $round;

    /** @Column(type="boolean") */
    protected $active;

    /** @Column(type="text") */
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