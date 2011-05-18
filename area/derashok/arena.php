<?php
/**
 * PvP Kampfarena
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: arena.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Page Content
 */
$page->set("pagetitle", "Derashok Kampfarena");
$page->set("headtitle", "Derashok Kampfarena");

$page->nav->add(new Link("Navigation"));
$page->nav->add(new Link("Aktualisieren", $page->url));

$battle = new Battle();

if ($user->char->isInABattle()) {
    include (DIR_INCLUDES."helpers/battle.running.php");
} elseif (BattleSystem::getBattleList()) {
    $page->nav->add(new Link("Zurück", "page=derashok/tribalcenter"));
    include (DIR_INCLUDES."helpers/battle.list.php");
} else {
    $page->nav->add(new Link("Zurück", "page=derashok/tribalcenter"));
    include (DIR_INCLUDES."helpers/battle.create.php");
}
?>
