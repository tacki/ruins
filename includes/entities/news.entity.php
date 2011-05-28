<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="news")
 */
class News extends \EntityBase
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

    /** @Column(length=64) */
    protected $title;

    /** @Column(type="text") */
    protected $body;

    /** @Column(length=64) */
    protected $area;

    public function __construct()
    {
        $this->date = new \DateTime;
        $this->area = "GLOBAL";
    }
}