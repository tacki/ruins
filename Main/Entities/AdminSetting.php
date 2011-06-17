<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="administration")
 */
class Administration extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=64) */
    protected $name;

    /** @Column(length=32) */
    protected $category;

    /** @Column(length=255) */
    protected $page;
}
?>