<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="chat")
 */
class Chat extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(type="datetime") */
    protected $date;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $author;

    /** @Column(type="text") */
    protected $chatline;

    /** @Column(length=32) */
    protected $section;

    /** @Column(type="integer") */
    protected $status;

    public function __construct()
    {
        // Default Values
        $this->date = new \DateTime;
        $this->status = 0;
    }
}