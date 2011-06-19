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
use Main\Entities;

/**
 * Global Includes
 */
require_once(DIR_BASE."main.inc.php");

/**
 * Rights Manager Class
 *
 * Class to manage Group- and Characterrights
 * @package Ruins
 */
class Rights
{
    /**
     * Create a Group
     * @param string $groupname
     * @return Entities\Group
     */
    public static function createGroup($groupname)
    {
        global $em;

        $group = $em->getRepository("Main:Group")
                    ->findOneBy(array("name" => $groupname));

        if ($group) return $group;

        $newgroup = new Entities\Group;
        $newgroup->name = $groupname;

        $em->persist($newgroup);
        $em->flush();

        return $newgroup;
    }

    /**
     * Get Group
     * @param string|Entities\Group $groupname
     * @return Entities\Group Group Object
     */
    public static function getGroup($groupname)
    {
        return self::_getGroupObject($groupname);
    }

    /**
     * Remove a Group
     * @param string|Entities\Group $groupname
     */
    public static function removeGroup($groupname)
    {
        global $em;

        $group = self::_getGroupObject($groupname);

        $em->remove($group);
    }

    /**
     * Check if given Character is in Group
     * @param string|Entities\Group $groupname
     * @param Entities\Character $character
     * @return bool
     */
    public static function isInGroup($groupname, Entities\Character $character)
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
     * @param string|Entities\Group $groupname
     * @param Entities\Character $character
     */
    public static function addToGroup($groupname, Entities\Character $character)
    {
        if (!self::isInGroup($groupname, $character)) {
            $group = self::_getGroupObject($groupname);

            $character->groups->add($group);
        }
    }

    /**
     * Remove Character from Group
     * @param string|Entities\Group $groupname
     * @param Entities\Character $character
     */
    public static function removeFromGroup($groupname, Entities\Character $character)
    {
        $group = self::_getGroupObject($groupname);

        $character->groups->removeElement($group);
    }

    /**
     * Get Group Object (if it's not already one)
     * @param string|Entities\Group $groupname
     * @returns Entities\Group Group Object
     */
    private static function _getGroupObject($groupname)
    {
        global $em;

        if ($groupname instanceof Entities\Group) {
            $group = $groupname;
        } else {
            $group = $em->getRepository("Main:Group")
                        ->findOneBy(array("name" => $groupname));
        }

        return $group;
    }
}