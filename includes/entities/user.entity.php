<?php
namespace Entities;
use Doctrine\Common\Collections\ArrayCollection;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="users")
 */
class User extends \EntityBase
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

    /** @Column(type="object", nullable=true) */
    protected $iplist;

    /** @Column(type="object", nullable=true) */
    protected $uniqueid;

    /** @Column(type="boolean") */
    protected $loggedin;

    public function __construct()
    {
        $debuglog    = new ArrayCollection();
    }
}
?>