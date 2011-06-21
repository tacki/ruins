<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use Main\Layers\Money;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="banks")
 */
class Bank extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $name;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Main\Entities\Character
     */
    protected $depositor;

    /**
     * Layer (Main\Layers\Money)
     * @Column(type="integer")
     * @var Main\Layers\Money
     */
    protected $balance;


    public function __construct()
    {
        $this->balance = 0;
    }

    /** @PostLoad @PostUpdate @PostPersist */
    public function initLayers()
    {
        if (!($this->balance instanceof Money))
            $this->balance = new Money($this->balance);
    }

    /** @PreUpdate @PrePersist */
    public function endLayers()
    {
        if ($this->balance instanceof Money)
            $this->balance = $this->balance->endLayer();
    }
}