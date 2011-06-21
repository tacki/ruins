<?php
/**
 * Namespaces
 */
namespace Modules\Survey\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="modules__survey__answer")
 */
class Answer extends \Main\Entities\EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Poll", inversedBy="answers")
     * @var Modules\Survey\Entities\Poll
     */
    protected $poll;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $text;

    /**
     * @OneToMany(targetEntity="Vote", mappedBy="answer", cascade={"all"}, fetch="LAZY")
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $votes;


    public function __construct()
    {
        // Default Values
        $this->date = new DateTime;
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection;
    }
}
?>