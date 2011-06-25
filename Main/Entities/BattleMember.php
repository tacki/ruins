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
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Battle", inversedBy="members")
     * @var Main\Entities\Battle
     */
    protected $battle;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Main\Entities\Character
     */
    protected $character;

    /**
     * @Column(length=16)
     * @var string
     */
    protected $side;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $speed;

    /**
     * @Column(type="boolean")
     * @var bool
     */
    protected $actiondone;

    /**
     * @Column(type="boolean")
     */
    protected $token;

    /**
     * @Column(type="integer")
     * @var int
     */
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