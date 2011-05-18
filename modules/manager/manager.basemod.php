<?php
/**
 * Manager Module Basemod
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: manager.basemod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Manager Module Basemod
 *
 * @package Ruins
 */
abstract class Manager extends Module
{
    /**
     * The managed value
     * @var mixed
     */
    public $managedvalue;

    /**
     * Set the Managed Value
     * @param mixed $property Value to manage
     */
    public function setManagedValue($property)
    {
        $this->managedvalue = $property;
    }

    /**
     * Return the previously given Managed Value
     * Make sure that it has the same Datatype!
     * @return mixed Managed Value
     */
    public function getManagedValue()
    {
        return $this->managedvalue;
    }
}
?>
