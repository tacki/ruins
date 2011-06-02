<?php
/**
 * Namespaces
 */
namespace Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="users")
 */
class User extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=32, unique=true) */
    protected $login;

    /** @Column(length=32) */
    protected $password;

    /**
     * @OneToOne(targetEntity="Character")
     */
    protected $character;

    /**
     * @OneToOne(targetEntity="UserSetting")
     */
    protected $settings;

    /**
     * @OneToMany(targetEntity="DebugLog", mappedBy="user")
     */
    protected $debuglog;

    /** @Column(type="datetime") */
    protected $lastlogin;

    /**
     * @OneToMany(targetEntity="UserIP", mappedBy="user")
     */
    protected $iplist;

    /**
     * @OneToMany(targetEntity="UserUniqueID", mappedBy="user")
     */
    protected $uniqueidlist;

    /** @Column(type="boolean") */
    protected $loggedin;

    public function __construct()
    {
        // Default Values
        $this->lastlogin    = new DateTime();
        $this->loggedin     = false;
    }
}
?>