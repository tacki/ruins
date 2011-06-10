<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="users__settings")
 */
class UserSetting extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @OneToOne(targetEntity="Character")
     */
    protected $default_character;

    /** @Column(length=32) */
    protected $chat_dateformat;

    /** @Column(type="boolean") */
    protected $chat_censorship;

    /** @Column(type="array") */
    protected $chat_hide;

    public function __construct()
    {
        // Default Values
        $this->chat_dateformat = "[H:i:s]";
        $this->chat_censorship = true;
        $this->chat_hide = array();
    }
}
?>