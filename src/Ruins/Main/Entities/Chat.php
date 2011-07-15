<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use DateTime;
use Ruins\Main\Entities\EntityBase;

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
     * @var Ruins\Main\Entities\Character
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