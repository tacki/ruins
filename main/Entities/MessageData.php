<?php
/**
 * Namespaces
 */
namespace Entities;
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
     */
    protected $id;

    /** @Column(length=255) */
    protected $subject;

    /** @Column(type="text") */
    protected $text;

    public function __construct()
    {
        // Default Values
        $this->subject = "";
        $this->text = "";
    }
}