<?php
/**
 * Ironlance City Square
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Link;

/**
 * Page Content
 */
$page->set("pagetitle", "Ironlance Stadtzentrum");
$page->set("headtitle", "Ironlance Stadtzentrum");

$page->nav->add(new Link("Navigation"));
$page->nav->add(new Link("Reisen", "page=common/travel&return={$page->url->short}"));
$page->nav->add(new Link("Stadtbank", "page=ironlance/citybank"));
$page->nav->add(new Link("Spielerliste", "page=common/charlist&return={$page->url->short}"));

$page->nav->add(new Link("Ausrüstung", "page=common/equipment&return={$page->url->short}"));

$page->nav->add(new Link("Allgemein"));
$page->nav->add(new Link("Logout", "page=common/logout"));

$page->output("Willkommen im Stadtzentrum von Ironlance, dem aus Stein gebauten Stolz der menschlichen Rasse.`n");

$page->addChat("ironlance_citysquare");
$page->ironlance_citysquare->show();
?>
