<?php
/**
 * PvP Kampfarena
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Link,
    Main\Controller\Battle,
    Main\Manager;

/**
 * Page Content
 */
$page->set("pagetitle", "Derashok Kampfarena");
$page->set("headtitle", "Derashok Kampfarena");

$page->nav->add(new Link("Navigation"));
$page->nav->add(new Link("Aktualisieren", $page->url));

$battle = new Battle;

if ($battleid = Manager\Battle::getBattleID($user->character)) {
    $battle->load($battleid);
    include (DIR_INCLUDES."helpers/battle.running.php");
} elseif (Manager\Battle::getBattleList()) {
    $page->nav->add(new Link("Zurück", "page=derashok/tribalcenter"));
    include (DIR_INCLUDES."helpers/battle.list.php");
} else {
    $page->nav->add(new Link("Zurück", "page=derashok/tribalcenter"));

    $page->output("Zur Zeit läuft kein Kampf! Willst du einen provozieren?");
    $battle->addCreateBattleNav();
}
?>
