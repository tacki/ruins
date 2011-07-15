<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;

/**
 * @Entity
 * @Table(name="groups")
 */
class Group extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(length=64, unique=true)
     * @var string
     */
    protected $name;

    /**
     * @ManyToMany(targetEntity="Character", mappedBy="groups", cascade={"persist", "remove"})
     * @var Ruins\Main\Entities\Character
     */
    protected $character;
}