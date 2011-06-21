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
     * @var int
     */
    protected $id;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $votedate;

    /**
    * @ManyToOne(targetEntity="Main\Entities\Character")
    * @var Main\Entities\Character
    */
    protected $voter;

    /**
    * @ManyToOne(targetEntity="Poll")
    * @var Modules\Survey\Entities\Poll
    */
    protected $poll;

    /**
     * @ManytoOne(targetEntity="Answer", inversedBy="votes")
     * @var Modules\Survey\Entities\Answer
     */
    protected $answer;


    public function __construct()
    {
        // Default Values
        $this->votedate     = new DateTime;
    }
}
?>