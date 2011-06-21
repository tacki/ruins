<?php
/**
 * Namespaces
 */
namespace Main\Entities;

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
     * @var Main\Entities\Character
     */
    protected $character;
}