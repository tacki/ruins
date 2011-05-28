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
namespace Manager;

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Rights Manager Class
 *
 * Class to manage Group- and Characterrights
 * @package Ruins
 */
class Rights
{
    public static function createGroup($groupname)
    {
        global $em;

        $group = $em->getRepository("Entities\Group")
                    ->findOneBy(array("name" => $groupname));

        if ($group) return false;

        $newgroup = new \Entities\Group;
        $newgroup->name = $groupname;

        $em->persist($newgroup);
        $em->flush();
    }

    public static function removeGroup($groupname)
    {
        global $em;

        $group = $em->getRepository("Entities\Group")
                    ->findOneBy(array("name" => $groupname));

        $em->remove($group);
    }

    public static function addToGroup($groupname, \Entities\Character $character)
    {
        global $em;

        $group = $em->getRepository("Entities\Group")
                    ->findOneBy(array("name" => $groupname));

        $character->groups->add($group);
    }

    public static function removeFromGroup($groupname, \Entities\Character $character)
    {
        global $em;

        $group = $em->getRepository("Entities\Group")
                    ->findOneBy(array("name" => $groupname));

        $character->groups->removeElement($group);
    }
}