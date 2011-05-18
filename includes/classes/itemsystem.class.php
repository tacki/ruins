<?php
/**
 * Item Systemclass
 *
 * Class to manage all kind of Items
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: itemsystem.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Class Defines
 */
define("ITEMSYSTEM_CLASS_DEFAULT", 			"default");
define("ITEMSYSTEM_CLASS_WEAPON", 			"weapon");
define("ITEMSYSTEM_CLASS_WEAPON_BLADE", 	"weapon_blade");
define("ITEMSYSTEM_CLASS_WEAPON_BLUNT", 	"weapon_blunt");
define("ITEMSYSTEM_CLASS_WEAPON_RANGED", 	"weapon_ranged");
define("ITEMSYSTEM_CLASS_WEAPON_STAFF", 	"weapon_staff");
define("ITEMSYSTEM_CLASS_ARMOR", 			"armor");
define("ITEMSYSTEM_CLASS_ARMOR_HEAD", 		"armor_head");
define("ITEMSYSTEM_CLASS_ARMOR_CHEST", 		"armor_chest");
define("ITEMSYSTEM_CLASS_ARMOR_ARMS", 		"armor_arms");
define("ITEMSYSTEM_CLASS_ARMOR_LEGS", 		"armor_legs");
define("ITEMSYSTEM_CLASS_ARMOR_FEET", 		"armor_feet");
define("ITEMSYSTEM_LOCATION_BACKPACK", 		"backpack");
define("ITEMSYSTEM_LOCATION_EQUIPMENT",		"equipment");

/**
 * Item Systemclass
 *
 * Class to manage all kind of Items
 * @package Ruins
 */
class ItemSystem
{
    /**
     * Get Item Data (plain) as Object
     * @param int $itemid ID of the item to retrieve
     * @return Item The Item as an Object
     */
    public function getItemData($itemid)
    {
        $tempitem = new Item;
        $tempitem->load($itemid);
        $result = $tempitem;

        return $result;
    }


    /**
     * Get Item Object (including overloaded Items Objects like armor or weapon)
     * @param int $itemid ID of the item to retrieve
     * @return Item|Itemoverload The Item Object (Item or child of Itemoverload)
     */
    public function getItemObject($itemid)
    {
        $dbqt = new QueryTool();

        // First get the Class of this Item
        if (!$itemobject = SessionStore::readCache("itemdata_".$itemid."_class")) {

            $qResult = $dbqt->select("class")
                            ->from("items")
                            ->where("id=".$itemid)
                            ->exec()
                            ->fetchOne();

            if ($qResult) {
                if (strpos($qResult, '_') !== false) {
                    // Get item baseclass (for example 'weapon' or 'armor')
                    $basename = array_shift(explode("_", $qResult));

                    $itemobject = ucfirst($basename);
                } else {
                    $itemobject = "Item";
                }
            } else {
                $itemobject = "Item";
            }

            SessionStore::writeCache("itemdata_".$itemid."_class", $itemobject);
        }

        // Get the Id of the corresponding table for the overloaded item
        if ($itemobject != "Item") {
            // the itemtable is always the same as the itemclass + plural 's'
            $itemtable = strtolower($itemobject."s");

            if (!$overloadid = SessionStore::readCache("itemdata_".$itemid."_overloaded_id")) {
                $dbqt->clear();

                $qResult = $dbqt->select("id")
                                ->from($itemtable)
                                ->where("itemid=".$itemid)
                                ->exec()
                                ->fetchOne();

                if ($qResult) {
                    $overloadid = $qResult;
                } else {
                    $overloadid = false;
                }

                SessionStore::writeCache("itemdata_".$itemid."_overloaded_id", $overloadid);
            }
        }

        // Create an Instance of the class and return it
        // Use the overloadedid if the item is an overloaded one
        // else use the itemid
        $tempitem = new $itemobject;
        if (isset($overloadid)) {
            $tempitem->load($overloadid);
        } else {
            $tempitem->load($itemid);
        }
        $result = $tempitem;

        return $result;
    }

    /**
     * Get the equipped Item of the given Character
     * @param Character $char Character
     * @param string $class Itemclass to get (defaults to equipped weapon)
     * @return Item The equipped Item as an object
     */
    public function getEquippedItem(Character $char, $class=ITEMSYSTEM_CLASS_WEAPON)
    {
        if ($tempresult	= self::getInventoryList($char, ITEMSYSTEM_LOCATION_EQUIPMENT, $class)) {
            // Get the first Item found (should be only one)
            $result		= array_shift($tempresult);

            // Return instance of this Object
            return self::getItemObject($result['id']);
        } else {
            return false;
        }
    }

    /**
     * Get Inventory of the given Character
     * @param Character $char Character
     * @param string $location Filter by the location
     * @param string $class Filter by the itemclass
     * @return array 2-dimensional Array
     */
    public function getInventoryList(Character $char, $location=false, $class=false, $order="id", $orderDesc=false)
    {
        if (!$result = SessionStore::readCache("inventory_".$char->id."_".$location."_".$class."_".$order."_".$orderDesc)) {
            $dbqt = new QueryTool();

            $dbqt	->select("*")
                    ->from("items")
                    ->where("owner=".$char->id);

            if ($location) {
                $dbqt->where("location LIKE ".$dbqt->quote($location."%"));
            }

            if ($class) {
                $dbqt->where("class LIKE ".$dbqt->quote($class."%"));
            }

            // Set Order
            $dbqt->order($order, $orderDesc);

            if ($qResult = $dbqt->exec()->fetchAll()) {
                $result = $qResult;
            } else {
                $result = array();
            }

            SessionStore::writeCache("inventory_".$char->id."_".$location."_".$class."_".$order."_".$orderDesc, $result);
        }

        return $result;
    }

    public function getInventoryListAsObjects(Character $char, $location=false, $class=false, $order="id", $orderDesc=false)
    {
        // Get item baseclass (for example 'weapon' or 'armor')
        $itemobject = array_shift(explode("_", $class));

        // the itemtable is always the same as the itemclass + plural 's'
        $itemtable	= strtolower($itemobject) . "s";

        $dbqt = new QueryTool();

        $dbqt	->select("$itemtable.*")
                ->from($itemtable)
                ->where("owner=".$char->id);

        if ($itemtable != "items") {
            $dbqt->join("items", "$itemtable.itemid = items.id");
        }

        if ($location) {
            $dbqt->where("items.location LIKE ".$dbqt->quote($location."%"));
        }
        if ($class) {
            $dbqt->where("items.class LIKE ".$dbqt->quote($class."%"));
        }

        // Set Order
        $dbqt->order($order, $orderDesc);

        return dbResultAsObjects($dbqt, $itemobject);
    }
}
?>
