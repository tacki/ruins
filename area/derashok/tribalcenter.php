<?php
/**
 * Derashok Tribal Center
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
$page->set("pagetitle", "Derashok Stammeszentrum");
$page->set("headtitle", "Derashok Stammeszentrum");

$page->nav->addHead("Navigation")
          ->addLink("Reisen", "page=common/travel&return={$page->url->short}")
          ->addLink("Thagigdash Bogoob", "page=derashok/bogoob")
          ->addLink("Spielerliste", "page=common/charlist&return={$page->url->short}")
          ->addLink("Kampfarena", "page=derashok/arena");

$page->nav->addHead("Allgemein")
          ->addLink("Logout", "page=common/logout");

$page->output("Willkommen auf dem Stammeszentrum in Derashok, einem wichtigen Treffpunkt aller orkischen Clans.`n");

$page->output("`n`n");

$page->addChat("derashok_tribalcenter")->show();
?>
