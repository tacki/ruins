<?php
/**
 * Namespaces
 */
namespace Ruins\Modules\Survey\Entities;
use DateTime;
use Ruins\Main\Entities\EntityBase;

/**
 * @Entity
 * @Table(name="modules__survey__vote")
 */
class Vote extends EntityBase
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
    * @ManyToOne(targetEntity="Ruins\Main\Entities\Character")
    * @var Ruins\Main\Entities\Character
    */
    protected $voter;

    /**
    * @ManyToOne(targetEntity="Poll")
    * @var Ruins\Modules\Survey\Entities\Poll
    */
    protected $poll;

    /**
     * @ManytoOne(targetEntity="Answer", inversedBy="votes")
     * @var Ruins\Modules\Survey\Entities\Answer
     */
    protected $answer;


    public function __construct()
    {
        // Default Values
        $this->votedate     = new DateTime;
    }
}
?>