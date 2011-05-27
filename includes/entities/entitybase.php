<?php
/** @MappedSuperclass */
class EntityBase
{
    public function __isset($name)
    {
        if (isset($this->$name)) {
            return true;
        } else {
            return false;
        }
    }

    public function __get($name)
    {
        if (isset($this->$name) || is_null($this->$name)) {
            return $this->$name;
        } else {
            var_dump($this);
            echo "__get(): the property ".strtolower(get_class($this))."->$name doesn't exist!";
            die;
        }
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}