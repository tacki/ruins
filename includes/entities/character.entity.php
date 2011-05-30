<?php
namespace Entities;
use Doctrine\Common\Collections\ArrayCollection;

require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="characters")
 */
class Character extends \EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User")
     */
    protected $user;

    /** @Column(length=255) */
    protected $name;

    /** @Column(length=255) */
    protected $displayname;

    /** @Column(type="integer") */
    protected $level;

    /** @Column(type="integer") */
    protected $healthpoints;

    /** @Column(type="integer") */
    protected $lifepoints;

    /** @Column(type="integer") */
    protected $strength;

    /** @Column(type="integer") */
    protected $dexterity;

    /** @Column(type="integer") */
    protected $constitution;

    /** @Column(type="integer") */
    protected $intelligence;

    /** @Column(type="integer") */
    protected $charisma;

    /** @Column(type="integer") */
    protected $money;

    /**
     * @ManyToMany(targetEntity="Group", inversedBy="character")
     * @JoinTable(name="character_groups",
     *      joinColumns={@JoinColumn(name="character_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     */
    protected $groups;

    /** @Column(type="text") */
    protected $current_nav;

    /** @Column(type="array") */
    protected $allowednavs;

    /** @Column(type="array") */
    protected $allowednavs_cache;

    /** @Column(length=32) */
    protected $template;

    /** @Column(length=32, nullable=true) */
    protected $type;

    /** @Column(type="boolean") */
    protected $loggedin;

    /** @Column(length=32, nullable=true) */
    protected $race;

    /** @Column(length=32, nullable=true) */
    protected $profession;

    /** @Column(length=32, nullable=true) */
    protected $sex;

    /** @Column(type="datetime") */
    protected $lastpagehit;

    /** @Column(type="integer", nullable=true) */
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
        $this->intelligence      = 7;
        $this->charisma          = 7;
        $this->money             = 100;
        $this->current_nav       = "page=ironlance/citysquare";
        $this->allowednavs       = array();
        $this->allowednavs_cache = array();
        $this->template          = "default";
        $this->type              = NULL;
        $this->loggedin          = false;
        $this->race              = NULL;
        $this->profession        = NULL;
        $this->sex               = NULL;
        $this->lastpagehit       = new \DateTime();
        $this->debugloglevel     = 0;
    }
}
?>