<?php
/**
 * Interface for Modules
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Interfaces;
use Main\Controller\Page;

/**
 * Interface for Modules
 * @package Ruins
 */
interface Module
{
    /**
     * Module initialization
     */
    public function init();

    /**
     * Module Name
     */
    public function getModuleName();

    /**
     * Module Description
     */
    public function getModuleDescription();

    /**
     * Event triggered before Page Header is called
     */
    public function prePageHeader();

    /**
     * Event triggered before Page Content and after Page Header is called
     */
    public function prePageContent();

    /**
     * Event triggered before Page is generated
     * @param Page $page Page Object
     */
    public function prePageGeneration(Page $page);

    /**
     * Event triggered after Page is fully generated and before Content is shown by Template Engine
     * @param Page $page Page Object
     */
    public function postPageGeneration(Page $page);
}
?>