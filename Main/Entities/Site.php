<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="site")
 */
class Site extends EntityBase
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
     * @Column(type="text")
     * @var string
     */
    protected $description;

    /**
     * @OneToOne(targetEntity="Waypoint", mappedBy="site", cascade={"all"})
     * @var Main\Entities\Waypoint
     */
    protected $waypoint;
}
?>