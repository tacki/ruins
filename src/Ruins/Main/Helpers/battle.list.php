<?php
/**
 * Battlelist Helper
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Ruins\Main\Manager\BattleManager;

$battle->addCreateBattleNav();
$page->output("`nGegenwärtige Kämpfe`n");

$battlelist = $em->getRepository("Main:Battle")->getList();

foreach ($battlelist as $activebattle) {
    BattleManager::showBattleInformationBox($activebattle);
}
?>
