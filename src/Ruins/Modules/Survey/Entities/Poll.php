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
 * @Table(name="modules__survey__poll")
 */
class Poll extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(type="boolean")
     * @var bool
     */
    protected $active;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $creationdate;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $deadline;

    /**
     * @ManyToOne(targetEntity="Ruins\Main\Entities\Character")
     * @var Ruins\Main\Entities\Character
     */
    protected $creator;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $question;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $description;

    /**
     * @OneToMany(targetEntity="Answer", mappedBy="poll", cascade={"all"}, fetch="LAZY")
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $answers;


    public function __construct()
    {
        // Default Values
        $this->active         = true;
        $this->creationdate   = new DateTime;
        $this->answers        = new ArrayCollection;
    }
}
?>