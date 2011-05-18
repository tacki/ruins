<?php
/**
 * Running Battle Helper
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: battle.running.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

// Global Battle Nav();
$battle->addBattleNav();

// Show Roundnumber
$page->output("`c`b~~~ Runde " . $battle->round . " ~~~`b`c");

// Battle Timer (if active)
if ($battletimer = $battle->getTimer()) {
    $page->output("<div class='floatright'>Timer: " . $battletimer . "</div>", true);
}

// Battle Member List
$battle->showBattleMemberList(true);

// Divider
$page->output("`n<div style='border-bottom: 1px solid;'></div>`n", true);

// Skill Options
$page->output("`bAktion: `b`n");
$battle->showSkillChooser();
$page->output("`n");

// Divider
$page->output("`n<div style='border-bottom: 1px solid;'></div>`n", true);

// Battle Messages
$page->output("`bNachrichten: `b`n");

foreach ($battle->getResultMessages() as $resultmessage) {
        $page->output($resultmessage['date'] . " " . $resultmessage['message'] ."`n");
}

//$battle->getActionDoneList();

?>
