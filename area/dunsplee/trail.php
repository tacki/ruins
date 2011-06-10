<?php
/**
 * Trail to Dunsplee Forest
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
$page->set("pagetitle", "Dunsplee Waldweg");
$page->set("headtitle", "Dunsplee Waldweg");

$page->nav->add(new Link("Navigation"));
$page->nav->add(new Link("Reisen", "page=common/travel&return={$page->url->short}"));
$page->nav->add(new Link("Weiher", "page=dunsplee/pond"));
$page->nav->add(new Link("Spielerliste", "page=common/charlist&return={$page->url->short}"));

$page->nav->add(new Link("Allgemein"));
$page->nav->add(new Link("Logout", "page=common/logout"));

$page->output("Du stehst auf einem kleinen Weg, kurz bevor dieser in den dichten Dunsplee Wald verschwindet.
                `n Die Gegend hier ist nicht sehr einladend, leicht bedrohlich. Doch einen tapferen Recken
                wie dich wird das doch nicht abschrecken, oder?`n");

$page->output("`n`n`n");

$page->addChat("dunsplee_trail");
$page->dunsplee_trail->show();
?>
