<?php
/**
 * Interface for Navigation Objects
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Interfaces;

/**
 * Interface for Navigation Objects
 * @package Ruins
 */
interface NavigationInterface
{
    /**
     * Get Navigation Links as array
     * @var array
     */
    public function getLinkList();
}
?>