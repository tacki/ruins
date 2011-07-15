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
use Ruins\Main\Controller\Link;

/**
 * Page Content
 */
$page->set("pagetitle", "Ironlance Stadtzentrum");
$page->set("headtitle", "Ironlance Stadtzentrum");

$page->nav->addHead("Navigation")
          ->addLink("Reisen", "page=common/travel&return={$page->url->short}")
          ->addLink("Stadtbank", "page=ironlance/citybank")
          ->addLink("Spielerliste", "page=common/charlist&return={$page->url->short}")
          ->addLink("Ausrüstung", "page=common/equipment&return={$page->url->short}");

$page->nav->addHead("Allgemein")
          ->addLink("Logout", "page=common/logout");

$page->output("Willkommen im Stadtzentrum von Ironlance, dem aus Stein gebauten Stolz der menschlichen Rasse.`n");

$page->addChat("ironlance_citysquare")->show();
?>
