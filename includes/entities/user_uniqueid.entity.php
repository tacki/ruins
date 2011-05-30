<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="users_uniqueids")
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
        $this->date = new \DateTime;
    }
}
?>