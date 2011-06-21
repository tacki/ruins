<?php
/**
 * Namespaces
 */
namespace Main\Entities;
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
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Main\Entities\Character
     */
    protected $sender;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Main\Entities\Character
     */
    protected $receiver;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $date;

    /**
     * @ManyToOne(targetEntity="MessageData", inversedBy="messages")
     * @var Main\Entities\MessageData
     */
    protected $data;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $status;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
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