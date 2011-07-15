<?php
/**
 * Base Repository
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Main\Repositories;
use Ruins\Main\Entities\EntityBase;
use Ruins\Common\Controller\Error;
use Doctrine\ORM\EntityRepository;

/**
 * Base Repository
 * @package Ruins
 */
class Repository extends EntityRepository
{

    /**
     * (INTERN) Primary Entity Name
     * @var string
     */
    const PRIMARY = "primary";

    /**
     * Entity Holder
     * @var array
     */
    private $_entities = array();

    /**
     * Our Reference Object
     * @var mixed
     */
    private $_referenceObject;

    /**
     * Set a Reference Object
     * @param mixed $object
     * @return Ruins\Main\Repositories\Repository
     */
    public function setReference($object)
    {
        $this->_referenceObject = $object;
        return $this;
    }

    /**
     * Get the Reference Object
     * @return mixed
     */
    public function getReference()
    {
        return $this->_referenceObject;
    }

    /**
     * Retrieve Entity
     * @param string $name
     * @return Ruins\Main\Entities\EntityBase:
     */
    public function getEntity($name=self::PRIMARY)
    {
        return $this->_entities[$name];
    }

    /**
     * Set primary Entity for this Repository
     * @param Ruins\Main\Entities\EntityBase $entity
     */
    public function setEntity(EntityBase $entity)
    {
        $this->_entities[self::PRIMARY] = $entity;
    }

    /**
     * Add another Entity
     * @param Ruins\Main\Entities\EntityBase $entity
     * @param string $name
     * @throws Error
     */
    public function addEntity(EntityBase $entity, $name)
    {
        if ($name == self::PRIMARY) throw new Error("Name '". self::PRIMARY . "' is now allowed!");

        $this->_entities[$name] = $entity;
    }


}