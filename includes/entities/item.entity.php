<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="items")
 */
class Item extends \EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=255) */
    protected $name;

    /** @Column(length=64) */
    protected $class;

    /** @Column(type="integer") */
    protected $level;

    /** @Column(type="integer") */
    protected $requirement;

    /** @Column(type="integer") */
    protected $weight;

    /** @Column(type="integer") */
    protected $value;

    /** @Column(length=64) */
    protected $location;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $owner;
}