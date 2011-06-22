<?php
/**
 * Namespaces
 */
namespace Modules\Support\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="modules__support")
 */
class SupportRequests extends \Main\Entities\EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $date;

    /**
     * @ManyToOne(targetEntity="Main\Entities\User")
     * @var Main\Entities\User
     */
    protected $user;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $email;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $charactername;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $text;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $pagedump;

    public function __construct()
    {
        // Default Values
        $this->date = new DateTime;
    }
}
?>