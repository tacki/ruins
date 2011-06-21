<?php
/**
 * Namespaces
 */
namespace Modules\Survey\Entities;
use DateTime;

/**
 * @Entity
 * @Table(name="modules__survey__poll")
 */
class Poll extends \Main\Entities\EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(type="boolean") */
    protected $active;

    /** @Column(type="datetime") */
    protected $creationdate;

    /** @Column(type="datetime") */
    protected $deadline;

    /**
     * @ManyToOne(targetEntity="Main\Entities\Character")
     */
    protected $creator;

    /** @Column(length=255) */
    protected $question;

    /** @Column(length=255) */
    protected $description;

    /**
     * @OneToMany(targetEntity="Answer", mappedBy="poll", cascade={"all"}, fetch="LAZY")
     */
    protected $answers;


    public function __construct()
    {
        // Default Values
        $this->active         = true;
        $this->creationdate   = new DateTime;
        $this->answers        = new \Doctrine\Common\Collections\ArrayCollection;
    }
}
?>