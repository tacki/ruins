<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use DateTime;
use Ruins\Main\Entities\EntityBase;

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
     * @ManyToOne(targetEntity="Battle", inversedBy="messages")
     * @var Ruins\Main\Entities\Battle
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
    }
}
?>