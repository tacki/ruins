<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="chat")
 */
class Chat extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $date;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Main\Entities\Character
     */
    protected $author;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $chatline;

    /**
     * @Column(length=32)
     * @var string
     */
    protected $section;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $status;

    public function __construct()
    {
        // Default Values
        $this->date = new DateTime;
        $this->status = 0;
    }
}