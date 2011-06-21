<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="openid")
 */
class OpenID extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="User")
     * @var Main\Entities\User
     */
    protected $user;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $urlID;
}
?>