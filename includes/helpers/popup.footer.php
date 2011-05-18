<?php
/**
 * Popup-Footer
 *
 * Common Popup-Footer
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: popup.footer.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */


/**
 * Popup Content
 */
// save the user - every change to $user below this line will be doomed :P
if ($popup->nav instanceof Nav && $popup->nav->isloaded) $popup->nav->save();
if ($user->char instanceof Character && $user->char->isloaded) $user->char->save();
if ($user instanceof User && $user->isloaded) $user->save();

// compile the page
$popup->show();
?>
