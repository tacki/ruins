<?php
/**
 * Basic Module Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: module.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Basic Module Class
 *
 * @package Ruins
 */
abstract class Module
{
    /**
     * The parental Object (if any)
     * @var mixed
     */
    protected $parent = false;

    /**
     * Return Module Name
     * @return string Module Name
     */
    public function getModuleName() { return "Undefined Module"; }

    /**
     * Return short Module Description
     * @return string Module Description
     */
    public function getModuleDescription() { return "Undefined Module Description"; }

    /**
     * Initialize the Module
     */
    public function init() { }

    /**
     * Install the Module
     * This is called during Installation & after enabling a Module.
     * Can be used to add Database-Entries or prepare the Filesystem etc.
     */
    public function install() { }

    /**
     * Initialize the parental Object
     * @param mixed $parent The parental Object
     */
    public function initParent($parent)
    {
        $this->parent = $parent;
    }
}
?>
