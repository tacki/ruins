<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="debuglog")
 */
class DebugLog extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="debuglog")
     * @var Main\Entities\User
     */
    protected $user;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $text;
}
?>