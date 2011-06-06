<?php
/**
 * Namespaces
 */
namespace Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="message")
 */
class Message extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Character", cascade={"persist", "remove"})
     */
    protected $sender;

    /**
     * @ManyToOne(targetEntity="Character", cascade={"persist", "remove"})
     */
    protected $receiver;

    /** @Column(type="datetime") */
    protected $date;

    /**
     * @ManyToOne(targetEntity="MessageData", cascade={"persist", "remove"})
     */
    protected $data;

    /** @Column(type="integer") */
    protected $status;

    /** @Column(type="datetime") */
    protected $statuschange;

    public function __construct()
    {
        // Default Values
        $this->status = 0;
        $this->date = new DateTime();
        $this->statuschange = new DateTime();
    }
}
?>