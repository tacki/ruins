<?php
/**
 * Logout Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: logout.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

if ($user instanceof User && $user->char instanceof Character) {
    $user->debuglog->add("Logout");
    $user->logout();
}
$page->nav->redirect("page=common/login");

?>
