<?php
/**
 * Base Object Class
 *
 * Class to create a simple array-based Object.
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;
use Common\Controller\Error;

/**
 * Base Object Class
 *
 * Class to create a simple array-based Object.
 * @package Ruins
 */
class BaseObject
{

    /**
     * Loaded-Flag
     * @var bool
     */
    public $isloaded;

    /**
     * Properties-Data
     * @var array
     */
    protected $properties;

    /**
     * Show all Warnings
     * Default: false
     * @var bool
     */
    protected $showwarning;

    /**
     * Validate if a Property exists while set
     * Default: false
     * @var bool
     */
    protected $validproperty;

    /**
     * Notifier of direct changes through __set()
     * @var array
     */
    protected $properties_modified;

    /**
     * Additional Snapshot to get changes via reference
     * @var string Serialized Array
     */
    protected $properties_snapshot;

    /**
     * Array of overloaded Properties
     * @var array
     */
    protected $properties_overload;

    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings = false)
    {
        // Initialization
        $this->isloaded = false;
        $this->properties = array();
        $this->properties_modified = array();
        $this->properties_snapshot = "";
        $this->properties_overload = array();

        // Class Settings
        if (isset($settings['showwarning'])) {
            $this->showwarning = $settings['showwarning'];
        } else {
            $this->showwarning = false;
        }
        if (isset($settings['validproperty'])) {
            $this->validproperty = $settings['validproperty'];
        } else {
            $this->validproperty = false;
        }
    }

    /**
     * Get Value Overload
     * @param string $name Name of the value to get
     * @return mixed The requested value
     */
    final public function &__get($name)
    {
        $null = NULL;

        if ($this->isloaded) {
            if (array_key_exists($name, $this->properties_overload)
                && method_exists($this, "_overload_$name")
                && array_key_exists($name, $this->properties)) {
                $result = $this->{"_overload_".$name}($this->properties[$name]);
                return $result;
            } elseif (array_key_exists($name, $this->properties)) {
                return $this->properties[$name];
            } elseif ($this->showwarning) {
                echo "__get(): the property ".strtolower(get_class($this))."->$name doesn't exist!";
                die;
            }  else {
                return $null;
            }
        }

        return $null;
    }

    /**
     * Set Value Overload
     * @param string $name Name of the value to set
     * @param mixed $value Value to set
     */
    final public function __set($name, $value)
    {
        if ($this->isloaded) {
            if (($this->validproperty && array_key_exists($name, $this->properties)) || !$this->validproperty) {
                $this->properties[$name] = $value;
                //$this->properties_modified[$name] = $value;
            } elseif ($this->showwarning) {
                echo "__set(): the property ".strtolower(get_class($this))."->$name doesn't exist!";
                die;
            }
        }
    }

    /**
     * IsSet Value Overload
     * @param string $name Name of the value to check if set
     * @return bool true if set, else false
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Sleep Function - called by serialize()
     * Clean up Object
     * @return bool Array of properties to serialize
     */
    public function __sleep()
    {
        // now serialize everything in this class...
        $sleepvars = array_keys( (array)$this );

        return $sleepvars;
    }

    /**
     * Wakeup Function - called by unserialize()
     * Rearrange temporary values
     */
    public function __wakeup()
    {

    }

    /**
     * Add a new Property to the class (throw Error if already existing)
     * @param string $propertyname Name of the Property to add
     * @param mixed $value Propertyvalue
     * @param bool $ignoreExisting Don't throw an Error if this Property already exists
     */
    protected function addProperty($propertyname, $value, $ignoreExisting = false)
    {
        if (!$this->validproperty) {
            if (!isset($this->properties[$propertyname])) {
                $this->properties[$propertyname] = $value;
            } elseif (!$ignoreExisting) {
                throw new Error("Can't add Property (already set!)");
            }
        } else {
            throw new Error("Can't add a Property if validproperty is set!");
        }
    }

    /**
     * Remove a Property from the class if validproperty isn't set
     * @param string $propertyname Name of the Property to remove
     */
    protected function delProperty($propertyname)
    {
        if (!$this->validproperty) {
            unset ($this->properties[$propertyname]);
        } else {
            throw new Error("Can't delete a Property if validproperty is set!");
        }
    }

    /**
     * Load the data from an Array
     * @param array $data Data to fill the object with
     * @return bool true if successful, else false
     */
    public function load($data, $fields=false)
    {
        // PreLoad-Modules
        $this->mod_preload();

        // load data
        if ($fields && is_array($fields) && is_array($data)) {
            $this->properties = array_intersect($data, $fields);
        } elseif (is_array($data)) {
            $this->properties = $data;
        } else {
            return false;
        }

        // Set isloaded-flag
        $this->isloaded = true;

        // Load-Modules
        $this->mod_postload();

        // Create Properties Snapshot (needed to detect changes)
        $this->createSnapshot();

        return true;
    }

    /**
     * adds some after-loading features for this class.
     */
    protected function mod_preload()  {

    }

    /**
     * adds some after-loading features for this class.
     */
    protected function mod_postload()  {

    }

    /**
     * Create a snapshot of the current Properties
     */
    protected function createSnapshot() {
        $this->properties_snapshot = serialize($this->properties);
    }

    /**
     * Unload the data an clean the object
     */
    public function unload()
    {
        $this->properties_snapshot = "";
        $this->properties_modified = array();
        $this->properties = array();

        $this->isloaded = false;
    }

    /**
     * Clear all temporary values
     */
    public function cleanup()
    {
        $this->properties_modified = array();
    }

    /**
     * Clear all data
     */
    public function clear()
    {
        $this->properties 			= array();
        $this->properties_snapshot 	= "";
        $this->properties_modified 	= array();
    }

    /**
     * Import properties
     * @param array $array Array to fill the object with
     */
    public function import($array)
    {
        if (is_array($array)) {
            $this->properties = $array;

            $this->isloaded = true;
        } else {
            return false;
        }
    }

    /**
     * Export the current properties
     */
    public function export()
    {
        if ($this->isloaded) {
            return $this->properties;
        } else {
            return false;
        }
    }

    /**
     * Get the changed values of the Object as an Array
     * @return array An Array with the Fields that have changed since the loading
     */
    protected function getChangedProperties()
    {
        //var_dump($this->properties_snapshot);

        if ($this->isloaded && strlen($this->properties_snapshot)) {
            // create temp-function to compare objects in array
            $args	= array($this->properties, unserialize($this->properties_snapshot));
            $args[]	= create_Function('$a, $b', 'return strcmp(serialize($a),serialize($b));');

            // this has definitely changed
            $changedproperties = call_user_func_array('array_udiff', $args);

            foreach ($this->properties as $key => $value) {
                // Check if a sub-baseobject exists and it has some changedproperties
                if (($value instanceof BaseObject) && count($value->getChangedProperties())) {
                    $changedproperties[$key] = $value;
                }
            }

            return $changedproperties;
        } else {
            return false;
        }
    }

    /**
     * Save current data
     */
    public function save()
    {
        if ($this->isloaded) {
            // Call Save-Mod
            $this->mod_presave();

            // first unload all Managers
            if (is_array($this->managers)) {
                foreach ($this->managers as $propertyname=>$manager) {
                    $this->unsetPropertyManager($propertyname);
                }
            }

            // we don't have another datasource to save to...
            return true;
        }

        $this->mod_postsave();
    }

    /**
     * adds some pre-saving features for this class.
     */
    protected function mod_presave()
    {
    }

    /**
     * adds some post-saving features for this class.
     */
    protected function mod_postsave()
    {
    }
}
?>
