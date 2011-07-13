<?php
/**
 * Rights Manager Class
 *
 * Class to manage Group- and Characterrights
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Manager;
use Main\Entities\Group;
use Main\Entities\Character;
use Common\Controller\Registry;

/**
 * Rights Manager Class
 *
 * Class to manage Group- and Characterrights
 * @package Ruins
 */
class RightsManager
{
    /**
     * Create a Group
     * @param string $groupname
     * @return Group
     */
    public static function createGroup($groupname)
    {
        $em = Registry::getEntityManager();

        $group = $em->getRepository("Main:Group")
                    ->findOneBy(array("name" => $groupname));

        if ($group) return $group;

        $newgroup = new Group;
        $newgroup->name = $groupname;

        $em->persist($newgroup);
        $em->flush();

        return $newgroup;
    }

    /**
     * Get Group
     * @param string|Group $groupname
     * @return Group Group Object
     */
    public static function getGroup($groupname)
    {
        return self::_getGroupObject($groupname);
    }

    /**
     * Remove a Group
     * @param string|Group $groupname
     */
    public static function removeGroup($groupname)
    {
        $em = Registry::getEntityManager();

        $group = self::_getGroupObject($groupname);

        $em->remove($group);
    }

    /**
     * Check if given Character is in Group
     * @param string|Group $groupname
     * @param Character $character
     * @return bool
     */
    public static function isInGroup($groupname, Character $character)
    {
        $group = self::_getGroupObject($groupname);

        if ($character->groups->contains($group)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add Character to Group
     * @param string|Group $groupname
     * @param Character $character
     */
    public static function addToGroup($groupname, Character $character)
    {
        if (!self::isInGroup($groupname, $character)) {
            $group = self::_getGroupObject($groupname);

            $character->groups->add($group);
        }
    }

    /**
     * Remove Character from Group
     * @param string|Group $groupname
     * @param Character $character
     */
    public static function removeFromGroup($groupname, Character $character)
    {
        $group = self::_getGroupObject($groupname);

        $character->groups->removeElement($group);
    }

    /**
     * Get Group Object (if it's not already one)
     * @param string|Group $groupname
     * @returns Group Group Object
     */
    private static function _getGroupObject($groupname)
    {
        $em = Registry::getEntityManager();

        if ($groupname instanceof Group) {
            $group = $groupname;
        } else {
            $group = $em->getRepository("Main:Group")
                        ->findOneBy(array("name" => $groupname));
        }

        return $group;
    }
}