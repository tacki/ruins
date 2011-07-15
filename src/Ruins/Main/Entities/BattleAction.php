<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;

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
     * @var Ruins\Main\Entities\Battle
     */
    protected $battle;

    /**
     * @OneToOne(targetEntity="BattleMember", inversedBy="action")
     * @var Ruins\Main\Entities\BattleMember
     */
    protected $initiator;

    /**
     * @Column(type="array")
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $targets;

    /**
     * @ManyToOne(targetEntity="Skill")
     * @var Ruins\Main\Entities\Skill
     */
    protected $skill;

    public function __construct()
    {
        // Default Values
        $this->targets = new \Doctrine\Common\Collections\ArrayCollection;
    }
}