<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="skills")
 */
class Skill extends EntityBase
{
    /**
    * @Id @Column(type="integer")
    * @GeneratedValue
    * @var int
    */
    protected $id;
}
?>