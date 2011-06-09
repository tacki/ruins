<?php
/**
 * Modulesystem Class
 *
 * Class to manage Modules
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: modulesystem.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Namespaces
 */
use Controller\Page;

/**
 * Class Defines
 */
define("MODULESYSTEM_FILE_EXTENSION", ".mod.php");

/**
 * Modulessystem Class
 *
 * Class to manage Modules
 * @package Ruins
 */
class ModuleSystem
{
    /**
     * Parental Object
     * @var object
     */
    static protected $parent;

    /**
     * Set the Parent to provide the Modules some Information about their parent
     * @param object $parent The Parental Object of this Module
     */
    public function setParent($parent)
    {
        self::$parent = $parent;
    }

    /**
     * Unset the Parent
     */
    public function unsetParent()
    {
        self::$parent = false;
    }

    /**
     * Check if the given parameter is a Module
     * @param mixed $object Object to check
     * @return bool true if this object implements a module, else false
     */
    public function isModule($object)
    {
        if ($object instanceof Module) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the List of Moduletypes
     * @return array List of Moduletypes
     */
    public function getModuleTypes()
    {
        $moduletypes 	= array();
        $dirlist		= getDirList(DIR_MODULES);

        return $dirlist['directories'];
    }

    /**
     * Get the List of Modules from the Filesystem, available for the given Moduletype
     * @param string $moduletype Moduletype
     * @return array List of Modulenames
     */
    public function getModuleListFromFilesystem($moduletype)
    {
        $modulelist = array();

        $dirlist 	= getDirList(DIR_MODULES.strtolower($moduletype), true);
        $dirlist 	= $dirlist['directories'];

        foreach ($dirlist as $dir) {
            // Get Name of the Module (last path-element)
            $modulename = array_pop(explode("/", $dir));

            // Check if the Module-File exists
            if (file_exists($dir . "/" . $modulename . MODULESYSTEM_FILE_EXTENSION)) {
                $modulelist[] = $modulename;
            }
        }

        return $modulelist;
    }

    /**
     * Get the List of Modules from the Database
     * @param string $moduletype Moduletype
     * @return array List of Modules (all Properties)
     */
    public function getModuleListFromDatabase($moduletype=false, $onlyenabled=false)
    {
        $dbqt = new QueryTool();

        $dbqt	->select("*")
                ->from("modules");

        if ($moduletype) {
            $dbqt->where("type=".$dbqt->quote($moduletype));
        }

        if ($onlyenabled) {
            $dbqt->where("enabled=1");
        }

        $result = $dbqt->exec()->fetchAll();

        return $result;
    }

    /**
     * Synchronize the Modulelist existing at the Database with the Modulelist existing in our Directory
     * The Filesystem has the higher Priority (of course!), so DB-Entries will be overwritten, even if both
     * sides changed.
     * @return bool true if successful, else false
     */
    public function syncModuleListToDatabase()
    {
        // First clear the Modules-Table
        $dbqt = new QueryTool();
        $dbqt->deletefrom("modules")->exec();

        foreach (self::getModuleTypes() as $moduletype) {
            $filesystem_list	= self::getModuleListFromFilesystem($moduletype);
            $database_list		= self::getModuleListFromDatabase($moduletype);

            $updateDatabase		= false;

            foreach ($filesystem_list as $modulename) {
                $tempmodule = self::_loadModule($moduletype, $modulename);

                // Check if the Module already exists at the Database and
                // set $updateDatabase to the existing id
                foreach ($database_list as $module) {
                    if ($module['name'] == $tempmodule->getModuleName() && $module['type'] == $moduletype) {
                        $updateDatabase = $module['id'];
                    }
                }

                // Create the correct Database Object to sync with the DB
                $databaseobject = array();
                if ($updateDatabase) {
                    $databaseobject['id']			= $updateDatabase;
                }
                $databaseobject['name'] 			= $tempmodule->getModuleName();
                $databaseobject['description'] 		= $tempmodule->getModuleDescription();
                $databaseobject['filesystemname']	= $modulename;
                $databaseobject['type']				= $moduletype;
                if (property_exists($tempmodule, "disabled")) {
                    $databaseobject['enabled']		= false;
                } else {
                    $databaseobject['enabled']		= true;
                }

                $dbqt = new QueryTool();

                $result = $dbqt	->insertinto("modules")
                                ->data($databaseobject)
                                ->exec();

                if ( $result === false ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Load a Module and install it
     * This is called during Installation & after enabling a Module.
     * @param string $moduletype Type of the Module
     * @param string $modulename Name of the Module
     */
    public function installModule($moduletype, $modulename)
    {
        $module = self::_loadModule($moduletype, $modulename);

        // only return false, if the install()-method also returned false
        // Everything else is OK (even no return -> void/null)
        if ($module->install() === false) {
            return false;
        }

        return true;
    }

    /**
     * Enable a Manager Module
     * @param mixed $property The Property to 'manage'
     * @param string $managername The Name of the Managermodule
     */
    public function enableManagerModule(&$property, $managername)
    {
        if ($property instanceof Manager) {
            return false;
        }

        $savevalue = $property;

        $property = self::_loadModule("Manager", $managername);
        $property->setManagedValue($savevalue);
    }

    /**
     * Disable a Manager Module
     * @param mixed $property The Property to 'unmanage'
     * @param string $managername The Name of the Managermodule
     */
    public function disableManagerModule(&$property)
    {
        if ($property instanceof Manager) {
            $property = $property->getManagedValue();
        }
    }

    /**
     * Enable Race Module for a given Character
     * @param Character $character The Character to use
     */
    public function enableRaceModule(Character &$character)
    {
        // the racename ($character->race at the db)
        // can be found at $character->race->getShortname()
        // so we can safely overwrite $character->race now
        $character->race = self::_loadModule("Race", $character->race);
        $character->race->setCharacterReference($character);
    }

    /**
     * Disable Race Module for a given Character
     * @param Character $character The Character to use
     */
    public function disableRaceModule(Character &$character)
    {
        if ($character->race instanceof Race) {
            $character->race = $character->race->getShortname();
        }
    }

    /**
     * Enable Output Module
     * @param Page $page The Pageobject to use
     * @param string $modulename Name of the Module
     */
    public function enableOutputModule(Page &$page, $modulename)
    {
        $module = self::_loadModule("Output", $modulename);
        $page->addOutputModule($modulename, $module);
    }

    /**
     * Disable Output Module
     * @param Page $page The Pageobject to use
     * @param string $modulename Name of the Module
     */
    public function disableOutputModule(Page &$page, $modulename)
    {
        $page->removeOutputModule($modulename);
    }

    /**
     * Return given Skill Module
     * @param string $skillname Name of the Skill to load
     * @return object The Skill-Module
     */
    public function getSkillModule($skillname)
    {
        return self::_loadModule("Skill", $skillname);
    }

    /**
     * Disable a Module regardless of its Type
     * @param Module $module The Module to disable
     */
    public function disableModule(&$module)
    {
        if ($module instanceof Manager) {
            self::disableManagerModule($module);
        } elseif ($module instanceof Race) {
            $module = $module->getShortname();
        }
    }

    /**
     * Load enabled Output Modules
     * @param Page $page The Page-Object to use
     */
    public function loadOutputModules(&$page)
    {
        $modulelist = self::getModuleListFromDatabase("output", true);

        foreach ($modulelist as $module) {
            self::enableOutputModule($page, $module['filesystemname']);
        }
    }

    /**
     * Load the Module Class
     * @param string $moduletype Moduletype
     * @param string $modulename Name of the Module to load
     * @return object Instance of the loaded Module
     */
    private function _loadModule($moduletype, $modulename)
    {
        // $moduletypeclass is the classname of the Moduletype => first char uppercase
        $moduletypeclass = ucfirst($moduletype);
        // $moduletype is the path-component => all lowercase
        $moduletype = strtolower($moduletype);
        // $classname is the name of the class => first char uppercase
        $classname = ucfirst($modulename);
        // $modulename is the path-component => all lowercase
        $modulename = strtolower($modulename);

        if (array_search($modulename, self::getModuleListFromFilesystem($moduletype)) === false) {
            throw new Error ("Module $modulename not found!");
        }

        // include the Mod-File
        require_once (DIR_MODULES . $moduletype . "/" . $modulename. "/" . $modulename . MODULESYSTEM_FILE_EXTENSION);

        if (class_exists($classname, false)) {

            $tempclass = new $classname;

            // Class exists, now check if the class extends the correct parent
            if ($tempclass instanceof $moduletypeclass && $tempclass instanceof Module) {
                // Module is correct and loaded, now
                $tempclass->initParent(self::$parent);
                $tempclass->init();
                return $tempclass;
            } else {
                throw new Error ("Module $classname is not an instance of $moduletypeclass");
            }
        }

    }
}
?>
