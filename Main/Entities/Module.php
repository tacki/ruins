<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="modules")
 */
class Module extends EntityBase
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
    protected $name;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $description;

    /**
     * @Column(type="text")
     * @var string
     */
    protected $basedir;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $classname;

    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $enabled;

    public function __construct()
    {
        // Default Values
        $this->type = "";
        $this->enabled = 0;
    }
}
?>