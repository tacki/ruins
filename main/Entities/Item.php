<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({
 * 					   "armors" = "Entities\Items\Armor",
 * 					   "commons" = "Entities\Items\Common",
 *                     "weapons" = "Entities\Items\Weapon"
 *                   })
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

    public function __construct()
    {
        // Default Values
        $this->level = 0;
        $this->requirement = 0;
        $this->weight = 0;
        $this->value = 0;
        $this->location = \Manager\Item::LOCATION_BACKPACK;
    }
}