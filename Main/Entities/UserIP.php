<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="users__ips")
 */
class UserIP extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="iplist")
     */
    protected $user;

    /** @Column(type="datetime") */
    protected $date;

    /** @Column(length=32) */
    protected $ip;

    public function __construct()
    {
        // Default Values
        $this->date = new DateTime;
    }
}
?>