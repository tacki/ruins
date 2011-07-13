<?php
/**
 * Namespaces
 */
namespace Main\Entities;

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

    public function __toString()
    {
        return (string)$this->id;
    }
}