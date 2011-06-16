<?php
/**
 * Page-Footer
 *
 * Common Page-Footer
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: page.footer.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Nav;

/**
 * Page Content
 */
// save the user - every change to $user below this line will be doomed :P
if ($page->nav instanceof Nav) $page->nav->save();

global $em;
$em->flush();

// compile the page
$page->show();
?>
