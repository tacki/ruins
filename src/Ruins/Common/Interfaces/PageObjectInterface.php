<?php
/**
* Interface for Page Objects
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
 * Interface for Page Objects
 * @package Ruins
 */
interface PageObjectInterface
{
    public function setTitle($title);

    public function createContent($page, $queryParameters);
}