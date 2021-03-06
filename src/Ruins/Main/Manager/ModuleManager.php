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
namespace Ruins\Main\Manager;
use Ruins\Main\Manager\SystemManager;
use Ruins\Common\Controller\Registry;
use Doctrine\DBAL\Types\Type;

/**
 * Modulesystem Class
 *
 * Class to manage Modules
 * @package Ruins
 */
class ModuleManager
{
    /**
     * Class Constants
     */
    const EVENT_PRE_PAGEHEADER      = "prePageHeader";
    const EVENT_PRE_PAGECONTENT     = "prePageContent";
    const EVENT_PRE_PAGEGENERATION  = "prePageGeneration";
    const EVENT_POST_PAGEGENERATION = "postPageGeneration";

    /**
     * Call Module
     * @param string $functionname Name of the Module-Event to call
     * @param object $object Optional Object
     */
    public static function callModule($eventname, $object=NULL)
    {
       foreach(self::getModuleListFromDatabase(true) as $module) {
            $classname = $module->classname;

            $module = new $classname;
            $module->$eventname($object);
        }
    }

    /**
     * Get the List of Modules from the Filesystem
     * @return array List of Modulenames
     */
    public static function getModuleListFromFilesystem()
    {
        $result = array();
        $dircontent = SystemManager::getDirList(DIR_MODULES);

        foreach($dircontent['directories'] as $dirname) {
            // Generate the Classname of the Module-Init-File
            $classname = "Ruins\\Modules\\".$dirname."\\".$dirname;
            if (self::validateModule($classname)) {
                $result[] = array ( "directory" => $dirname . "/", "classname" => $classname );
            }
        }

        return $result;
    }

    /**
     * Validate given Module-Class
     * @param string $initClass
     * @return bool true if Class is valid, else false
     */
    public static function validateModule($initClass)
    {
        if (class_exists($initClass, true)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the List of Modules from the Database
     * @param bool $onlyenabled Only enabled Modules
     * @return array List of Modules (all Properties)
     */
    public static function getModuleListFromDatabase($onlyenabled=false)
    {
        $em = Registry::getEntityManager();

        $qb = $em->createQueryBuilder();

        $qb ->select("module")
            ->from("Main:Module", "module");
        if ($onlyenabled) $qb->where("module.enabled = ?1")->setParameter(1, true, Type::BOOLEAN);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Clear ModuleList from Database
     */
    public static function clearModuleList()
    {
        $em = Registry::getEntityManager();

        $qb = $em->createQueryBuilder();

        $qb ->delete("Main:Module")
            ->getQuery()->execute();
    }

    /**
     * Synchronize the Modulelist existing at the Database with the Modulelist existing in our Directory
     * @return bool true if successful, else false
     */
    public function syncModuleListToDatabase()
    {
        $em = Registry::getEntityManager();

        $moduleFSList	= self::getModuleListFromFilesystem();
        $moduleDBList	= self::getModuleListFromDatabase();

        foreach($moduleFSList as $moduleFS) {
            $addFlag        = true;

            foreach($moduleDBList as $moduleDB) {
                if ($moduleDB->classname == $moduleFS['classname'] && $moduleDB->basedir == $moduleFS['directory']) {
                    $addFlag = false;
                }
            }

            if ($addFlag) {
                // execute init()-Method of unknown Module
                $module = new $moduleFS['classname'];
                $module->init();
            }
        }
        $em->flush();

        return true;
    }
}
?>