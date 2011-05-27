<?php
/**
 * Entity Handler Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * User Class
 *
 * User-Class
 * @package Ruins
 */
class EntityHandler
{
    protected $entity;

    public function loadEntity($entityname, $id)
    {
        global $em;

        $this->entity = $em->find($entityname, $id);
    }

    public function getEntity ()
    {
        return $this->entity;
    }

    public function __get($name)
    {
        return $this->entity->$name;
    }

    public function __set($name, $value)
    {
        $this->entity->$name = $value;
    }
}