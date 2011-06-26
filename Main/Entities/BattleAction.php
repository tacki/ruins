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
     * @ManyToOne(targetEntity="BattleMember")
     * @var Main\Entities\BattleMember
     */
    protected $initiator;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $target;

    /**
     * @ManyToOne(targetEntity="Skill")
     * @var Main\Entities\Skill
     */
    protected $skill;
}