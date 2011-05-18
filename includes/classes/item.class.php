<?php
/**
 * Item Class
 *
 * Base Item-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: item.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Item Class
 *
 * Base Item-Class
 * @package Ruins
 */
class Item extends DBObject
{
    /**
     * Buff Object
     * @var Buff
     */
    public $buff;

    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings = false)
    {
        // Call Constructor of the Parent-Class
        parent::__construct($settings);
    }

    /**
     * @see includes/classes/BaseObject#mod_postload()
     */
    public function mod_postload()
    {
        if ($this->buffid > 0) {
            $this->buff = new Buff;
            $this->buff->load($this->buffid);
        }

        ModuleSystem::enableManagerModule($this->value, "Money");
    }

    /**
     * @see includes/classes/BaseObject#mod_presave()
     */
    protected function mod_presave()
    {
        // prune corresponding cache
        // see ItemSystem::getItemObject
        SessionStore::pruneCache("itemdata_".$this->id."_*");
        // see ItemSystem::getInventoryList()
        SessionStore::pruneCache("inventory_*");
    }

}
?>
