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
namespace Main\Manager;
use Common\Controller\SessionStore,
    Main\Entities\Character;
use Common\Controller\Registry;

/**
 * Item Systemclass
 *
 * Class to manage all kind of Items
 * @package Ruins
 */
class Item
{
    const CLASS_DEFAULT        = "default";
    const CLASS_WEAPON         = "weapon";
    const CLASS_WEAPON_BLADE   = "weapon_blade";
    const CLASS_WEAPON_BLUNT   = "weapon_blunt";
    const CLASS_WEAPON_RANGED  = "weapon_ranged";
    const CLASS_WEAPON_STAFF   = "weapon_staff";
    const CLASS_ARMOR          = "armor";
    const CLASS_ARMOR_HEAD     = "armor_head";
    const CLASS_ARMOR_CHEST    = "armor_chest";
    const CLASS_ARMOR_ARMS     = "armor_arms";
    const CLASS_ARMOR_LEGS     = "armor_legs";
    const CLASS_ARMOR_FEET     = "armor_feet";
    const LOCATION_BACKPACK    = "backpack";
    const LOCATION_EQUIPMENT   = "equipment";

    /**
     * Get all defined Armor Classes
     * @return array
     */
    public function getArmorClasses()
    {
        return array (
                        self::CLASS_ARMOR_HEAD,
                        self::CLASS_ARMOR_ARMS,
                        self::CLASS_ARMOR_CHEST,
                        self::CLASS_ARMOR_LEGS,
                        self::CLASS_ARMOR_FEET,
                     );
    }

    /**
     * Get all defined Weapon Classes
     * @return array
     */
    public function getWeaponClasses()
    {
        return array (
                        self::CLASS_WEAPON_BLADE,
                        self::CLASS_WEAPON_BLUNT,
                        self::CLASS_WEAPON_RANGED,
                        self::CLASS_WEAPON_STAFF,
                     );
    }

    /**
     * Get Item Object (including overloaded Items Objects like armor or weapon)
     * @param int $itemid ID of the item to retrieve
     * @return Item|Itemoverload The Item Object (Item or child of Itemoverload)
     */
    public function getItem($itemid)
    {
        $em = Registry::getEntityManager();

        $result = $em->find("Main:Item", $itemid);

        return $result;
    }

    /**
     * Get the equipped Item of the given Character
     * @param Character $character Character
     * @param string $itemclass Itemclass to get
     * @return Item The equipped Item as an object
     */
    public function getEquippedItem($character, $itemclass)
    {
        if ($tempresult	= self::getInventoryList($character, self::LOCATION_EQUIPMENT, $itemclass)) {

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
     * @param string $itemclass Filter by the itemclass
     * @param string $order
     * @param string $orderDir
     * @return array 2-dimensional Array
     */
    public function getInventoryList($character, $location, $itemclass=false, $order="id", $orderDir="ASC")
    {
        $em = Registry::getEntityManager();

        $qb = $em->createQueryBuilder();

        if (is_array ($itemclass)) {
            $result = array();

            foreach($itemclass as $class) {
                $result = array_merge($result, self::getInventoryList($character, $location, $class, $order, $orderDir));
            }
        } else {
            $qb ->select("item")
                ->from("Main:Item", "item")
                ->where("item.owner = ?1")->setParameter(1, $character);
            if ($location != "all") $qb->andWhere("item.location = ?2")->setParameter(2, $location);
            if ($itemclass) $qb->andWhere("item.class LIKE ?3")->setParameter(3, $itemclass."%");
            $qb->orderBy("item.".$order, $orderDir);


            $query  = $qb->getQuery();
            $result =  $query->getResult();
        }

        return $result;
    }
}
?>
