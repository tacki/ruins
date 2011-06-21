<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use Main\Layers\Money,
    Main\Manager;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({
 * 					   "items"   = "Main\Entities\Item",
 * 					   "armors"  = "Main\Entities\Items\Armor",
 * 					   "commons" = "Main\Entities\Items\Common",
 *                     "weapons" = "Main\Entities\Items\Weapon"
 *                   })
 * @Table(name="items")
 */
abstract class Item extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(length=64)
     * @var string
     */
    protected $class;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $name;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $level;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $requirement;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $weight;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $value;

    /**
     * @Column(length=64)
     * @var string
     */
    protected $location;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Main\Entities\Character
     */
    protected $owner;

    public function __construct()
    {
        // Default Values
        $this->level = 0;
        $this->requirement = 0;
        $this->weight = 0;
        $this->value = 0;
        $this->location = Manager\Item::LOCATION_BACKPACK;
    }

    /** @PostLoad @PostUpdate @PostPersist */
    public function initLayers()
    {
        if (!($this->value instanceof Money))
            $this->value = new Money($this->value);
    }

    /** @PreUpdate @PrePersist */
    public function endLayers()
    {
        if ($this->value instanceof Money)
            $this->value = $this->value->endLayer();
    }
}