<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @Table(name="openid")
 */
class OpenID extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User")
     */
    protected $user;

    /** @Column(length=255) */
    protected $urlID;
}
?>