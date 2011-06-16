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
     */
    protected $id;

    /** @Column(length=255) */
    protected $name;

    /** @Column(type="text") */
    protected $description;

    /** @Column(type="text") */
    protected $basedir;

    /** @Column(length=255) */
    protected $classname;

    /** @Column(type="boolean") */
    protected $enabled;

    public function __construct()
    {
        // Default Values
        $this->type = "";
        $this->enabled = 0;
    }
}
?>