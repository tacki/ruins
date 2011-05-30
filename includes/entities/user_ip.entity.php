<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="users_iplist")
 */
class UserIPList extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User")
     */
    protected $user;

    /** @Column(type="DateTime") */
    protected $date;

    /** @Column(length=32) */
    protected $ip;

    public function __construct()
    {
        // Default Values
        $this->date = new \DateTime;
    }
}
?>