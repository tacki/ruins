<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @MappedSuperclass
 */
class EntityBase
{
    /*
    public function __isset($name)
    {
        if (isset($this->$name)) {
            return true;
        } else {
            return false;
        }
    }*/

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}