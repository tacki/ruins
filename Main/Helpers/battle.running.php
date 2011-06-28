<?php
/**
 * Running Battle Helper
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

// Global Battle Nav();
$battle->addBattleNav();

// Show Roundnumber
$page->output("`c`b~~~ Runde " . $battle->getRound() . " ~~~`b`c");

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
$battle->showSkillChooser($user->character);
$page->output("`n");

// Divider
$page->output("`n<div style='border-bottom: 1px solid;'></div>`n", true);

// Battle Messages
$page->output("`bNachrichten: `b`n");

foreach ($battle->getResultMessages() as $resultmessage) {
        $page->output($resultmessage->date->format("[H:i:s]") . " " . $resultmessage->message ."`n");
}

//$battle->getActionDoneList();

?>
