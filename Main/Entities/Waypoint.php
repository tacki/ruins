<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="waypoints")
 */
class Waypoint extends EntityBase
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
    protected $name;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $x;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $y;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $z;
}
?>