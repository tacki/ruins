<?php
/**
 * Logout Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

if ($user instanceof User) {
    $user->addDebugLog("Logout");
    $user->logout();
}
$page->nav->redirect("page=common/login");

?>
