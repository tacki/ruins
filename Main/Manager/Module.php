<?php
/**
 * Modulesystem Class
 *
 * Class to manage Modules
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
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
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Modulesystem Class
 *
 * Class to manage Modules
 * @package Ruins
 */
class Module
{
    /**
     * Get the List of Modules from the Filesystem
     * @return array List of Modulenames
     */
    public static function getModuleListFromFilesystem()
    {
        $result = array();
        $dircontent = System::getDirList(DIR_MODULES);

        foreach($dircontent['directories'] as $dirname) {
            // Generate the Classname of the Module-Init-File
            $result[] = "Modules\\".$dirname."\\".$dirname;
        }

        return $result;
    }

    /**
     * Get the List of Modules from the Database
     * @param string $moduletype Moduletype
     * @return array List of Modules (all Properties)
     */
    public static function getModuleListFromDatabase($onlyenabled=false)
    {
        $qb = getQueryBuilder();

        $qb ->select("module")
            ->from("Main:Module", "module");
        if ($onlyenabled) $qb->where("enabled = ?1")->setParameter(1, true);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Clear ModuleList from Database
     */
    public static function clearModuleList()
    {
        $qb = getQueryBuilder();

        $qb ->delete("Main:Module")
            ->getQuery()->execute();
    }

    /**
     * Synchronize the Modulelist existing at the Database with the Modulelist existing in our Directory
     * @return bool true if successful, else false
     */
    public function syncModuleListToDatabase()
    {
        global $em;

        $addFlag        = true;
        $moduleFSList	= self::getModuleListFromFilesystem();
        $moduleDBList	= self::getModuleListFromDatabase();

        foreach($moduleFSList as $moduleFS) {
            foreach($moduleDBList as $moduleDB) {

                if ($moduleDB->filesystemname == $moduleFS) {
                    $addFlag = false;
                }
            }

            if ($addFlag) {
                // execute init()-Method of unknown Module
                call_user_func($moduleFS."::init");
            }
        }
        $em->flush();

        return true;
    }
}
?>