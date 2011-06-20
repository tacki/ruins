<?php
/**
 * Namespaces
 */
namespace Modules\Survey\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="modules__survey__vote")
 */
class Vote extends \Main\Entities\EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(type="datetime") */
    protected $votedate;

    /**
    * @ManyToOne(targetEntity="Main\Entities\Character")
    */
    protected $voter;

    /**
    * @ManyToOne(targetEntity="Poll")
    */
    protected $poll;

    /**
     * @ManytoOne(targetEntity="Answer")
     */
    protected $answer;


    public function __construct()
    {
        // Default Values
        $this->votedate     = new DateTime;
    }
}
?>