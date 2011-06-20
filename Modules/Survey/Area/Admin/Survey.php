<?php
/**
 * Admin - Modules
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Manager;

/**
 * Page Content
 */
$page->set("pagetitle", "Module");
$page->set("headtitle", "Module");

$page->nav->addHead("Navigation")
          ->addLink("Zurück", "page=admin/main");

$page->output("No Administration atm.");

?>