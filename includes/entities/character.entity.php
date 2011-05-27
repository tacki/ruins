<?php
namespace Entities;
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

    /** @Column(type="object", nullable=true) */
    protected $rightgroups;

    /** @Column(type="text", nullable=true) */
    protected $current_nav;

    /** @Column(type="object", nullable=true) */
    protected $allowednavs;

    /** @Column(type="object", nullable=true) */
    protected $allowednavs_cache;

    /** @Column(length=32, nullable=true) */
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
}
?>