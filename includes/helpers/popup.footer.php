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
 * Namespaces
 */
use Main\Controller\Nav;

/**
 * Popup Content
 */
// compile the page
$popup->show();

// save the user - every change to $user below this line will be doomed :P
if ($popup->nav instanceof Nav && $popup->nav->isloaded) $popup->nav->save();

global $em;
$em->flush();
?>
