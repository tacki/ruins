<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="message__data")
 */
class MessageData extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @OneToMany(targetEntity="Message", mappedBy="data", orphanRemoval=true)
     * @var Main\Entities\Message
     */
    protected $messages;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $subject;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $text;

    public function __construct()
    {
        // Default Values
        $this->subject = "";
        $this->text = "";
    }
}