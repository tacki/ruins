<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="translations")
 */
class Translation extends EntityBase
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
    protected $system;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $humanreadable;
}