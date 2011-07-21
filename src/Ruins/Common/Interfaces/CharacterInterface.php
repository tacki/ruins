<?php
/**
 * Interface for Characters
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Interfaces;
use Ruins\Common\Interfaces\NavigationInterface;

/**
 * Interface for Characters
 * @package Ruins
 */
interface CharacterInterface
{
    /**
     * Get allowed Navigation
     * @return NavigationInterface
     */
    public function getAllowedNavigation();

    /**
     * Set allowed Navigation
     * @param NavigationInterface $navigation
     */
    public function setAllowedNavigation(NavigationInterface $navigation);
}
?>