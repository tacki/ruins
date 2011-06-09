<?php
/**
 * Derashok Tribal Center
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id$
 * @package Ruins
 */

/**
 * Namespaces
 */
use Controller\Link;

/**
 * Page Content
 */
$page->set("pagetitle", "Derashok Stammeszentrum");
$page->set("headtitle", "Derashok Stammeszentrum");

$page->nav->add(new Link("Navigation"));
$page->nav->add(new Link("Reisen", "page=common/travel&return={$page->url->short}"));
$page->nav->add(new Link("Thagigdash Bogoob", "page=derashok/bogoob"));
$page->nav->add(new Link("Spielerliste", "page=common/charlist&return={$page->url->short}"));
$page->nav->add(new Link("Kampfarena", "page=derashok/arena"));

$page->nav->add(new Link("Allgemein"));
$page->nav->add(new Link("Logout", "page=common/logout"));

$page->output("Willkommen auf dem Stammeszentrum in Derashok, einem wichtigen Treffpunkt aller orkischen Clans.`n");

$page->output("`n`n");

$page->addChat("derashok_tribalcenter");
$page->derashok_tribalcenter->show();
?>
