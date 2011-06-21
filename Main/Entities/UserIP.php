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
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="iplist")
     * @var Main\Entities\User
     */
    protected $user;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $date;

    /**
     * @Column(length=32)
     * @var string
     */
    protected $ip;

    public function __construct()
    {
        // Default Values
        $this->date = new DateTime;
    }
}
?>