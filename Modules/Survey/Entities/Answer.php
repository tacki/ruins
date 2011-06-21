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
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Poll", inversedBy="answers")
     */
    protected $poll;

    /** @Column(length=255) */
    protected $text;

    /**
     * @OneToMany(targetEntity="Vote", mappedBy="answer", cascade={"all"}, fetch="LAZY")
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