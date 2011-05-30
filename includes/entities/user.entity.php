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

    /** @Column(type="object") */
    protected $iplist;

    /** @Column(type="object") */
    protected $uniqueid;

    /** @Column(type="boolean") */
    protected $loggedin;

    public function __construct()
    {
        // Default Values
        $this->lastlogin    = new \DateTime();
        $this->iplist       = new \IPStack;
        $this->uniqueid     = new \UniqueIDStack();
        $this->loggedin     = false;
    }
}
?>