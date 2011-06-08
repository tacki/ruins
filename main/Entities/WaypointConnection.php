<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @Table(name="waypoints__connection")
 */
class WaypointConnection extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Waypoint"))
     */
    protected $start;

    /**
     * @ManyToOne(targetEntity="Waypoint"))
     */
    protected $end;

    /** @Column(type="float") */
    protected $difficulty;
}
?>