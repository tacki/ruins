<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;

/**
 * @Entity
 * @Table(name="administration")
 */
class Administration extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(length=64)
     * @var string
     */
    protected $name;

    /**
     * @Column(length=32)
     * @var string
     */
    protected $category;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $page;
}
?>