<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @Table(name="badwords")
 */
class Badword extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=255) */
    protected $badword;

    /** @Column(length=255) */
    protected $replacement;
}
?>