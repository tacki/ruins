<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;

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
     * @OneToOne(targetEntity="Site", inversedBy="waypoint")
     * @var Ruins\Main\Entities\Site
     */
    protected $site;

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