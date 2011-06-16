<?php
/**
 * Module Base Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Modules;
use ReflectionClass,
    Main\Controller\Page;

/**
 * Module Base Class
 *
 * @package Ruins
 */
class ModuleBase
{
    /**
     * Module Initialization
     */
    public function init()
    {
        global $em;
        $calledClass     = get_called_class();
        $reflectionClass = new ReflectionClass($calledClass);
        $directory       = basename(dirname($reflectionClass->getFilename())) . "/";

        $module                 = new \Main\Entities\Module;
        $module->name           = static::getModuleName();
        $module->description    = static::getModuleDescription();
        $module->basedir        = $directory;
        $module->classname      = $calledClass;

        $em->persist($module);
    }

    /**
     * @see Common\Interfaces.Module::prePageHeader()
     */
    public function prePageHeader() {}

    /**
     * @see Common\Interfaces.Module::prePageContent()
     */
    public function prePageContent() {}

    /**
     * @see Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page) {}

    /**
     * @see Common\Interfaces.Module::postPageGeneration()
     */
    public function postPageGeneration(Page $page) {}
}
?>