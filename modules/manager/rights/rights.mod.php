<?php
/**
 * Rights Class
 *
 * Class to hold Access Rights for an Object
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Common\Controller\Error;

/**
 * Rights Class
 *
 * Class to hold Access Rights for an Object
 * @package Ruins
 */
class Rights extends Manager
{
    /**
     * internal money-value
     * @var array
     */
    private $rightgroups;

    /**
     * Module Name
     * @see includes/interfaces/Module#getModuleName()
     */
    public function getModuleName()
    {
        return "Rights Manager";
    }

    /**
     * Module Description
     * @see includes/interfaces/Module#getModuleDescription()
     */
    public function getModuleDescription()
    {
        return "Extend the Right Property to handle the Rightgroups";
    }

    /**
     * Manager Module setter
     * @param array Array of Rights
     */
    public function setManagedValue($property)
    {
        $this->rightgroups = $property;
    }

    /**
     * Manager Module getter
     * @return int unsorted Money value
     */
    public function getManagedValue()
    {
        return $this->rightgroups;
    }

    /**
     * Return the Rightgroups
     * @return array
     */
    public function get()
    {
        if (!is_array($this->rightgroups)) {
            $this->rightgroups = array();
        }

        return $this->rightgroups;
    }

    /**
     * Set the rights to a complete set of rights
     * @param mixed $rights array or ';-separated-string' of rights
     * @return bool true if successful, else false
     */
    public function set($rights)
    {
        if ($this->_assign($rights)) {
            if ($this->parent instanceof Character) {
                $this->parent->debuglog->add("Set new Rights (now: " . implode(",", $this->rightgroups) . ")", "default");
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a group to the rights
     * @param string $group Name of the group to add
     * @return bool true if successful, else false
     */
    public function add($group)
    {
        if ($this->_assign($group, false)) {
            if ($this->parent instanceof Character) {
                $this->parent->debuglog->add("Add a new Rightgroup (now: " . implode(",", $this->rightgroups) . ")", "default");
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove a group from the rights
     * @param string $group Name of the group to remove
     * @return bool true if successful, else false
     */
    public function remove($group)
    {
        if ($this->_revoke($group)) {
            if ($this->parent instanceof Character) {
                $this->parent->debuglog->add("Removed a Rightgroup (now: " . implode(",", $this->rightgroups) . ")", "default");
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Assign rights to the internal group-list
     * @access private
     * @param mixed $rights array or ';-separated-string' of rights. Single strings work also
     * @param bool $replace Replace the current Grouplist
     * @return bool true if successful, else false
     */
    private function _assign($rights, $replace=true)
    {
        if (!is_array($this->rightgroups)) {
            $this->rightgroups = array();
        }

        switch (true) {
            case is_array($rights):
                if (!$replace) {
                    $rights = array_unique(array_merge($this->rightgroups, $rights));
                }
                $this->rightgroups = $rights;
                return true;

            case is_string($rights):
                if (!$replace) {
                    $rights = array_unique(array_merge($this->rightgroups, explode(";", $rights)));
                    $rights = implode(";", $rights);
                }
                $this->rightgroups = explode(";", $rights);
                return true;

            default:
                throw new Error("Tried to assign unsupported/invalid rights!");
                break;
        }
    }

    /**
     * Revoke rights from the internal group-list
     * @access private
     * @param mixed $rights array or ';-separated-string' of rights. Single strings work also
     * @return bool true if successful, else false
     */
    private function _revoke($rights)
    {
        if (!is_array($this->rightgroups)) {
            $this->rightgroups = array();
        }

        switch (true) {
            case is_array($rights):
                $rights = array_diff($this->rightgroups, $rights);

                $this->rightgroups = $rights;
                return true;

            case is_string($rights):
                $rights = array_diff($this->rightgroups, explode(";", $rights));

                $this->rightgroups = $rights;
                return true;

            default:
                throw new Error("Tried to revoke unsupported/invalid rights!");
                break;
        }
    }

}
?>
