<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;

/**
 * @Entity
 * @Table(name="badwords")
 */
class Badword extends EntityBase
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
    protected $badword;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $replacement;
}
?>