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
use Main\Controller\Battle,
    Main\Manager;

$battle->addCreateBattleNav();
$page->output("`nGegenwärtige Kämpfe`n");

$battlelist = Manager\Battle::getBattleList();

foreach ($battlelist as $activebattle) {
    Manager\Battle::showBattleInformationBox($activebattle);
}
?>
