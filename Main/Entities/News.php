<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="news")
 */
class News extends EntityBase
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
     * @Column(length=64)
     * @var string
     */
    protected $title;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $body;

    /**
     * @Column(length=64)
     * @var string
     */
    protected $area;

    public function __construct()
    {
        $this->date = new DateTime;
        $this->area = "GLOBAL";
    }
}