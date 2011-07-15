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
use Ruins\Main\Controller\Link;
use Ruins\Common\Controller\Registry;
use Ruins\Main\Controller\BattleController;

/**
 * Page Content
 */
$page->set("pagetitle", "Derashok Kampfarena");
$page->set("headtitle", "Derashok Kampfarena");

$page->nav->addHead("Navigation")
          ->addLink("Aktualisieren", $page->url);

$battle = new BattleController;
$em = Registry::getEntityManager();

if ($em->getRepository("Main:Character")->getBattle($user->character)) {
    include (DIR_MAIN."Helpers/battle.running.php");
} elseif ($em->getRepository("Main:Battle")->getList()) {
    $page->nav->addLink("Zurück", "page=derashok/tribalcenter");
    include (DIR_MAIN."Helpers/battle.list.php");
} else {
    $page->nav->addLink("Zurück", "page=derashok/tribalcenter");

    $page->output("Zur Zeit läuft kein Kampf! Willst du einen provozieren?");
    $battle->addCreateBattleNav();
}
?>
