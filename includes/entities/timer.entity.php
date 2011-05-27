<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="timers")
 */
class Timer extends \EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=64, unique=true) */
    protected $name;

    /** @Column(type="datetime") */
    protected $completiontime;

    /** @Column(type="integer") */
    protected $backup_ttc;

    public function __construct() {
        $this->backup_ttc = 0;
    }
}