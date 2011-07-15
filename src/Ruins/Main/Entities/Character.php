<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use DateTime;
use Ruins\Main\Entities\EntityBase;
use Ruins\Main\Layers\Money;

/**
 * @Entity(repositoryClass="Ruins\Main\Repositories\CharacterRepository")
 * @HasLifecycleCallbacks
 * @Table(name="characters")
 */
class Character extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User")
     * @var Ruins\Main\Entities\User
     */
    protected $user;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $name;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $displayname;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $level;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $healthpoints;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $lifepoints;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $strength;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $dexterity;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $constitution;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $wisdom;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $intelligence;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $charisma;

    /**
     * Layer (Main\Layers\Money)
     * @Column(type="integer")
     * @var Ruins\Main\Layers\Money
     */
    protected $money;

    /**
     * @ManyToMany(targetEntity="Group", inversedBy="character")
     * @JoinTable(name="characters__groups",
     *      joinColumns={@JoinColumn(name="character_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $groups;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $current_nav;

    /**
     * @Column(type="array")
     * @var array
     */
    protected $allowednavs;

    /**
     * @Column(type="array")
     * @var array
     */
    protected $allowednavs_cache;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $template;

    /**
     * @Column(length=32, nullable=true)
     * @var string
     */
    protected $type;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $creationdate;

    /**
     * @Column(type="boolean")
     * @var bool
     */
    protected $loggedin;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $lastlogin;

    /**
     * @Column(length=32, nullable=true)
     * @var string
     */
    protected $race;

    /**
     * @Column(length=32, nullable=true)
     * @var string
     */
    protected $profession;

    /**
     * @Column(length=32, nullable=true)
     * @var string
     */
    protected $sex;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $lastpagehit;

    /**
     * @Column(type="integer", nullable=true)
     * @var int
     */
    protected $debugloglevel;

    public function __construct()
    {
        // Default Values
        $this->level             = 1;
        $this->healthpoints      = 10;
        $this->lifepoints        = 10;
        $this->strength          = 7;
        $this->dexterity         = 7;
        $this->constitution      = 7;
        $this->wisdom            = 7;
        $this->intelligence      = 7;
        $this->charisma          = 7;
        $this->money             = 100;
        $this->groups            = new \Doctrine\Common\Collections\ArrayCollection;
        $this->current_nav       = "page=ironlance/citysquare";
        $this->allowednavs       = array();
        $this->allowednavs_cache = array();
        $this->template          = "Main/View/Templates/Default";
        $this->type              = NULL;
        $this->creationdate      = new DateTime();
        $this->loggedin          = false;
        $this->lastlogin         = new DateTime();
        $this->race              = NULL;
        $this->profession        = NULL;
        $this->sex               = NULL;
        $this->lastpagehit       = new DateTime();
        $this->debugloglevel     = 0;
    }

    /** @PostLoad @PostUpdate @PostPersist */
    public function initLayers()
    {
        if (!($this->money instanceof Money))
            $this->money = new Money($this->money);
    }

    /** @PreUpdate @PrePersist */
    public function endLayers()
    {
        if ($this->money instanceof Money)
            $this->money = $this->money->endLayer();
    }

    public function getSpeed()
    {
        // FIXME: Calculate Speed based on Dexterity and Race
        return 5;
    }

    /**
     * Care about everything needed for character login
     */
    public function login()
    {
        $this->loggedin = true;
        $this->lastlogin = new DateTime();
    }

    /**
     * Care about everything needed for character logout
     */
    public function logout()
    {
        $this->loggedin = false;
    }
}
?>