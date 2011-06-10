<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="battles__members")
 */
class BattleMember extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Battle")
     */
    protected $battle;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $character;

    /** @Column(length=16) */
    protected $side;

    /** @Column(type="integer") */
    protected $speed;

    /** @Column(type="boolean") */
    protected $actiondone;

    /** @Column(type="boolean") */
    protected $token;

    /** @Column(type="integer") */
    protected $status;

    public function __construct()
    {
        // Default Values
        $this->actiondone = false;
        $this->token      = false;
        $this->status     = 0;
    }
}
?>