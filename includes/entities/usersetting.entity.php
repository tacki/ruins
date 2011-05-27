<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="usersettings")
 */
class UserSetting extends \EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="User")
     */
    protected $userid;

    /**
     * @OneToOne(targetEntity="Character")
     */
    protected $default_character;

    /** @Column(length=32) */
    protected $chat_dateformat;

    /** @Column(type="boolean") */
    protected $chat_censorship;

    public function __construct()
    {
        // Default Values
        $this->chat_dateformat = "[H:i:s]";
        $this->chat_censorship = true;
    }
}
?>