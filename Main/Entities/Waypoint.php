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
     */
    protected $id;

    /** @Column(length=255) */
    protected $name;

    /** @Column(type="integer") */
    protected $x;

    /** @Column(type="integer") */
    protected $y;

    /** @Column(type="integer") */
    protected $z;
}
?>