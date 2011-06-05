<?php
/**
 * Namespaces
 */
namespace Layers;

/**
 * Money Layer Class
 *
 * Money-Class
 * @package Ruins
 */
class LayerBase
{
    protected $_snapshot;
    protected $_managedValue;

    public function initLayer(&$managedValue)
    {
        if (is_object($managedValue)) {
            throw new \Error("I can't layer Objects.. sorry!");
        }

        $this->_snapshot = $managedValue;
        $this->_managedValue = &$managedValue;
    }

    public function isModified()
    {
        if ($this->_snapshot === $this->_managedValue) {
            return false;
        } else {
            return true;
        }
    }

    public function endLayer()
    {
        return $this->_managedValue;
    }
}
?>