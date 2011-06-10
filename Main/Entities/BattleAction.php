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
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Battle")
     */
    protected $battle;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $initiator;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $target;

    /** @Column(length=255) */
    protected $skill;
}