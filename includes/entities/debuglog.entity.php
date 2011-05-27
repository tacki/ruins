<?php
namespace Entities;
require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="debuglog")
 */
class DebugLog extends \EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="debuglog")
     */
    protected $user;

    /** @Column(length=255) */
    protected $text;
}