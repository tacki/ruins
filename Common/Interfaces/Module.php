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
use Main\Controller\Nav;

/**
 * Interface for Modules
 * @package Ruins
 */
interface Module
{
    public function init();

    public function getModuleName();

    public function getModuleDescription();

    public function callNavModule(Nav &$nav);

    public function callTextModule(array &$body);
}
?>