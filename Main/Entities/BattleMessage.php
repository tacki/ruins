<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="battles__messages")
 */
class BattleMessage extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Battle")
     * @var Main\Entities\Battle
     */
    protected $battle;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $date;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $message;

    public function __construct()
    {
        // Default Values
        $this->date     = new DateTime();
        $this->message  = "";
    }
}
?>