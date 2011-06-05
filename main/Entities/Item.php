<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({"armors" = "Armor", "commons" = "Common", "weapons" = "Weapon"})
 * @Table(name="items")
 */
class Item extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=64) */
    protected $class;

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

    public function __construct() {
        // Default Values
    }
}