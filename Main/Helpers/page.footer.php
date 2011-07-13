<?php
/**
 * Page-Footer
 *
 * Common Page-Footer
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Nav;
use Common\Controller\Registry;

/**
 * Page Content
 */
// compile the page
$page->show();

// save the user - every change to $user below this line will be doomed :P
if ($page->nav instanceof Nav) $page->nav->save();

Registry::getEntityManager()->flush();
?>
