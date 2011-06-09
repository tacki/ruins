<?php
/**
 * Battlelist Helper
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: battle.list.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

$battle->addCreateBattleNav();
$page->output("`nGegenwärtige Kämpfe`n");

$battlelist = Manager\Battle::getBattleList();

foreach ($battlelist as $activebattle) {
    $tempbattle = new Controller\Battle;
    $tempbattle->load($activebattle->id);
    $tempbattle->showBattleInformation();
}
?>
