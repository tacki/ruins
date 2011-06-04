<?php
/**
 * Item Systemclass
 *
 * Class to manage all kind of Items
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Manager;
use SessionStore,
    Entities\Character;

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
class Item
{
    /**
     * Get Item Object (including overloaded Items Objects like armor or weapon)
     * @param int $itemid ID of the item to retrieve
     * @return Item|Itemoverload The Item Object (Item or child of Itemoverload)
     */
    public function getItem($itemid, $itemclass)
    {
        global $em;

        $itemclass = ucfirst($itemclass);

        if ($position = strpos($itemclass, "_")) {
            $itemclass = substr($itemclass, 0, $position);
        }

        $result = $em->find("Entities\\".$itemclass, $itemid);

        return $result;
    }

    /**
     * Get the equipped Item of the given Character
     * @param Character $character Character
     * @param string $itemclass Itemclass to get (defaults to equipped weapon)
     * @return Item The equipped Item as an object
     */
    public function getEquippedItem($character, $itemclass=ITEMSYSTEM_CLASS_WEAPON)
    {
        if ($tempresult	= self::getInventoryList($character, ITEMSYSTEM_LOCATION_EQUIPMENT, $itemclass)) {

            // Get the first Item found (should be only one)
            $result		= array_shift($tempresult);

            // Return instance of this Object
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get Inventory of the given Character
     * @param Character $character Character
     * @param string $location Filter by the location
     * @param string $class Filter by the itemclass
     * @return array 2-dimensional Array
     */
    public function getInventoryList($character, $location, $itemclass, $order="id", $orderDir="ASC")
    {
        $qb = getQueryBuilder();

        if (is_array ($itemclass)) {
            $result = array();

            foreach($itemclass as $class) {
                $result = array_merge($result, self::getInventoryList($character, $location, $class, $order, $orderDir));
            }
        } else {
            $entity = ucfirst($itemclass);

            if ($position = strpos($entity, "_")) {
                $entity = substr($entity, 0, $position);
            }

            $qb ->select("item")
                ->from("Entities\\".$entity, "item")
                ->where("item.owner = ?1")->setParameter(1, $character)
                ->andWhere("item.location = ?2")->setParameter(2, $location)
                ->andWhere("item.class LIKE ?3")->setParameter(3, $itemclass."%")
                ->orderBy("item.".$order, $orderDir);


            $query  = $qb->getQuery();
            $result =  $query->getResult();
        }

        return $result;
    }
}
?>
