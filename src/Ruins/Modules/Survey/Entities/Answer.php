<?php
/**
 * Namespaces
 */
namespace Ruins\Modules\Survey\Entities;
use DateTime;
use Ruins\Main\Entities\EntityBase;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="modules__survey__answer")
 */
class Answer extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Poll", inversedBy="answers")
     * @var Ruins\Modules\Survey\Entities\Poll
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
        $this->votes = new ArrayCollection;
    }
}
?>