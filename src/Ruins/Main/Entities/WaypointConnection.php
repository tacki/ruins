<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;

/**
 * @Entity
 * @Table(name="waypoints__connection")
 */
class WaypointConnection extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Waypoint"))
     * @var Ruins\Main\Entities\Waypoint
     */
    protected $start;

    /**
     * @ManyToOne(targetEntity="Waypoint"))
     * @var Ruins\Main\Entities\Waypoint
     */
    protected $end;

    /**
     * @Column(type="float")
     * @var float
     */
    protected $difficulty;
}
?>