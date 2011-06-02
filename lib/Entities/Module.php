<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @Table(name="modules")
 */
class Module extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=255) */
    protected $name;

    /** @Column(type="text") */
    protected $description;

    /** @Column(length=255) */
    protected $filesystemname;

    /** @Column(length=255) */
    protected $type;

    /** @Column(type="boolean") */
    protected $enabled;
}
?>