<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="battles__actions")
 */
class BattleAction extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Battle", inversedBy="actions")
     * @var Main\Entities\Battle
     */
    protected $battle;

    /**
     * @OneToOne(targetEntity="BattleMember", inversedBy="action")
     * @var Main\Entities\BattleMember
     */
    protected $initiator;

    /**
     * @Column(type="array")
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $targets;

    /**
     * @ManyToOne(targetEntity="Skill")
     * @var Main\Entities\Skill
     */
    protected $skill;

    public function __construct()
    {
        // Default Values
        $this->targets = new \Doctrine\Common\Collections\ArrayCollection;
    }
}