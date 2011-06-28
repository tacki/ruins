<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="timers")
 */
class Timer extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(length=64, unique=true)
     * @var string
     */
    protected $name;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $completiontime;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $backup_ttc;

    public function __construct($name=false)
    {
        // Default Values
        if ($name) $this->name = $name;
        $this->backup_ttc = 0;
        $this->completiontime = new DateTime;
    }
}