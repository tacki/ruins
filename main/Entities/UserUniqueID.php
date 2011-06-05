<?php
/**
 * Namespaces
 */
namespace Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="users__uniqueids")
 */
class UserUniqueID extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="uniqueidlist")
     */
    protected $user;

    /** @Column(type="datetime") */
    protected $date;

    /** @Column(length=32) */
    protected $uniqueid;

    public function __construct()
    {
        // Default Values
        $this->date = new DateTime;
    }
}
?>