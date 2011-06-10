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
     */
    protected $id;

    /** @Column(length=255) */
    protected $name;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $depositor;

    /**
     * Layer (Main\Layers\Money)
     * @Column(type="integer")
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