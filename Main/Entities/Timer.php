<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use DateTime;

/**
 * @Entity(repositoryClass="Main\Repositories\TimerRepository")
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

    /**
    * Check if a Timer is running
    * @return bool true if the timer is running, false if the timer is stopped
    */
    public function isRunning()
    {
        if ($this->backup_ttc > 0) {
            return false;
        } else {
            return true;
        }
    }
}