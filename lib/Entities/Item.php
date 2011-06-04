<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @MappedSuperclass
 */
class Item extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=255) */
    protected $name;

    /** @Column(type="integer") */
    protected $level;

    /** @Column(type="integer") */
    protected $requirement;

    /** @Column(type="integer") */
    protected $weight;

    /** @Column(type="integer") */
    protected $value;

    /** @Column(length=64) */
    protected $location;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $owner;
}