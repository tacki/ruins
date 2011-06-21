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
     * @var int
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="User")
     * @var Main\Entities\User
     */
    protected $user;

    /**
     * @OneToOne(targetEntity="Character")
     * @var Main\Entities\Character
     */
    protected $default_character;

    /**
     * @Column(length=32)
     * @var string
     */
    protected $chat_dateformat;

    /**
     * @Column(type="boolean")
     * @var bool
     */
    protected $chat_censorship;

    /**
     * @Column(type="array")
     * @var array
     */
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