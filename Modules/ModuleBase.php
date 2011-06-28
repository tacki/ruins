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
     * Module Entity
     * @var \Main\Entities\Module
     */
    private $entity;

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
        $module->name           = static::getName();
        $module->description    = static::getDescription();
        $module->basedir        = $directory;
        $module->classname      = $calledClass;

        $em->persist($module);

        $this->entity = $module;
    }

    /**
     * Return associated Module Entity
     * @return \Main\Entities\Module Module Entity
     */
    public function getEntity()
    {
        global $em;

        if (isset($this->entity)) {
            return $this->entity;
        } else {
            $result = $em->getRepository("Main:Module")->findOneByName(static::getName());
            if ($result) {
                $this->entity = $result;
                return $result;
            } else {
                throw Error("Module Entity for Module ". static::getName() . " not found!");
            }
        }
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