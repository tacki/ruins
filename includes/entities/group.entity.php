<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="groups")
 */
class Group extends \EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=64, unique=true) */
    protected $name;

    /**
     * @ManyToMany(targetEntity="Character", mappedBy="groups", cascade={"persist", "remove"})
     */
    protected $character;
}