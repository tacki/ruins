<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @Table(name="translations")
 */
class Translation extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=255) */
    protected $system;

    /** @Column(length=255) */
    protected $humanreadable;
}