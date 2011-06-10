<?php
/**
 * Namespaces
 */
namespace Modules\Support\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="support")
 */
class SupportRequests extends \Main\Entities\EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(type="datetime") */
    protected $date;

    /**
     * @ManyToOne(targetEntity="Main\Entities\User")
     */
    protected $user;

    /** @Column(length=255) */
    protected $email;

    /** @Column(length=255) */
    protected $charactername;

    /** @Column(type="text") */
    protected $text;

    /** @Column(type="text") */
    protected $pagedump;

    public function __construct()
    {
        // Default Values
        $this->date = new DateTime;
    }
}
?>