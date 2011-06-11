<?php
/**
 * Page-Header
 *
 * Common Page-Header
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: page.header.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Namespaces
 */
use Common\Controller\SessionStore,
    Main\Controller\Link,
    Main\Controller\Page,
    Main\Controller\Config;

/**
 * Page Content
 */

// Initialize Config-Class
$config = new Config();

// Load User if in Session
if ($userid = SessionStore::get('userid')) {
    $user = $em->find("Main:User",$userid);
}

// Page preparation
$config->addPublicPage(array(	"common/login",
                                "common/login&op=checkpw",
                                "common/logout",
                                "developer/test")
                            );
$config->addNoCachePage(array(	"common/portal" )
                            );

if (array_search($_GET['page'], $config->get("publicpages")) !== false) {
    // this is a public page!
    $page = new Page();

    // Create the Page
    $page->create();
} elseif (isset($user)) {
    // this is a private page and a user is loaded
    $page = new Page($user->character);

    if (array_search($_GET['page'], $config->get("nocachepages")) !== false) {
        $page->disableCaching();
    }


    // Check for Connection Timeout
    if ($user->hasConnectionTimeout()) {
        // Connection Timeout occurred
        // Redirect to logoutpage
        SessionStore::set("logoutreason", "Automatischer Logout: Verbindungs Timeout!");
        $page->nav->redirect("page=common/logout");
    } else {
        // Create the Page
        $page->create();

        // Update lastpagehit
        $user->character->lastpagehit = new DateTime();

        // Set current_nav if this is not the portal
        if (strpos($page->url, "page=common/portal") === false) {
            $user->character->current_nav = (string)$page->url;
        } elseif (!$user->character->current_nav) {
            $user->character->current_nav = "page=ironlance/citysquare";
        }
    }
} else {
    // this is a private page, but no user is loaded. Force to logout
    SessionStore::set("logoutreason", "Automatischer Logout: Nicht eingeloggt!");
    $page = new Page();
    $page->nav->redirect("page=common/logout");
}

// BtCode
$page->addCommonCSS("btcode.css");

// Timers
$page->addJavaScriptFile("timer.func.js");

// Global Java Functions
$page->addJavaScriptFile("global.func.js");

// Popups
$page->addJavaScriptFile("popup.func.js");

// Set Servertime on Page
$page->set("servertime", date("H:i:s"));
?>
