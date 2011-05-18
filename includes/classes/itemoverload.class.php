<?php
/**
 * Item-Overload Class
 *
 * Overload the Item-Class to handle extended Items like Weapons or Armors
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: itemoverload.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Item-Overload Class
 *
 * Overload the Item-Class to handle extended Items like Weapons or Armors
 * @package Ruins
 */
abstract class ItemOverload extends DBObject
{
    /**
     * Itemdata
     * @var Item
     */
    protected $_item;

    /**
     * ReferencedProperties
     * @var array
     */
    protected $_referencedProperties;

    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings = false)
    {
        // Call Constructor of the Parent-Class
        parent::__construct($settings);

        // Initialize Attributes
        $this->_item = new Item;
        $this->_referencedProperties = array();
    }

    /**
     * adds some after-loading features for this class.
     */
    protected function mod_postload()
    {
        // Call mod_postload from Parent-Class
        parent::mod_postload();

        // Get the Itemdata from Database
        $this->_item = ItemSystem::getItemData($this->itemid);

        // merge the data from the corresponding item into our weapon-data.
        // properties of this weaponclass overwrite properties of the itemclass
        foreach ($this->_item->export() as $propertyname=>$value) {
            if (!isset($this->properties[$propertyname])) {
                $this->properties[$propertyname] = &$this->_item->$propertyname;
                $this->_referencedProperties[] = $propertyname;
            }
        }

        // Add reference to the Buff which is handled separately
        $this->properties['buff'] = &$this->_item->buff;
        $this->_referencedProperties[] = 'buff';
    }

    /**
     * @see includes/classes/BaseObject#mod_presave()
     */
    protected function mod_presave()
    {
        // Call mod_presave from Parent-Class
        parent::mod_presave();

        // Save the itemdata
        $this->_item->save();

        // remove the merged itemdata
        foreach ($this->_referencedProperties as $propertyname) {
            unset($this->properties[$propertyname]);
        }

        // prune corresponding cache
        // see ItemSystem::getItemObject
        SessionStore::pruneCache("itemdata_".$this->id."_*");
        // see ItemSystem::getInventoryList()
        SessionStore::pruneCache("inventory_*");
    }

    /**
     * @see includes/classes/BaseObject#mod_postsave()
     */
    protected function mod_postsave()
    {
        // Call mod_load from Parent-Class
        parent::mod_postsave();

        // Restore the References to the item, so we have
        // the same state as before saving (which is baseobject standard behaviour)
        foreach ($this->_referencedProperties as $propertyname) {
            $this->properties[$propertyname] = &$this->_item->$propertyname;
        }

    }
}
?>
