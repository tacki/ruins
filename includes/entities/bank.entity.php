<?php
namespace Entities;

require_once 'entitybase.php';

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
     * Layer (\Layers\Money)
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
        if (!($this->balance instanceof \Layers\Money))
            $this->balance = new \Layers\Money($this->balance);
    }

    /** @PreUpdate @PrePersist */
    public function endLayers()
    {
        if ($this->balance instanceof \Layers\Money)
            $this->balance = $this->balance->endLayer();
    }
}