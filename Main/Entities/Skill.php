<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="skills")
 */
class Skill extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
    * @Column(length=255)
    * @var string
    */
    protected $classname;

    /**
     * @Column(length=32)
     * @var string
     */
    protected $name;

    /**
     * @Column(length=64)
     * @var string
     */
    protected $description;

    /**
     * @Column(length=16)
     * @var string
     */
    protected $type;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $nrOfTargets;

    /**
     * @Column(length=16)
     * @var string
     */
    protected $possibleTargets;
}
?>