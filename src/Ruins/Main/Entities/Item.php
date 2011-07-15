<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;
use Ruins\Main\Layers\Money;
use Ruins\Main\Manager\ItemManager;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({
 * 					   "items"   = "Ruins\Main\Entities\Item",
 * 					   "armors"  = "Ruins\Main\Entities\Items\Armor",
 * 					   "commons" = "Ruins\Main\Entities\Items\Common",
 *                     "weapons" = "Ruins\Main\Entities\Items\Weapon"
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
     * @var Ruins\Main\Entities\Character
     */
    protected $owner;

    public function __construct()
    {
        // Default Values
        $this->level = 0;
        $this->requirement = 0;
        $this->weight = 0;
        $this->value = 0;
        $this->location = ItemManager::LOCATION_BACKPACK;
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