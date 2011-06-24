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
     * @OneToOne(targetEntity="Site", inversedBy="waypoint")
     * @var Main\Entities\Site
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