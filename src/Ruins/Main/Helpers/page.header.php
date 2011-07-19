<?php
/**
 * Page-Header
 *
 * Common Page-Header
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Ruins\Common\Controller\SessionStore;
use Ruins\Main\Controller\Link;
use Ruins\Main\Controller\Page;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Manager\RequestManager;

/**
 * Page Content
 */
// Load User if in Session
if ($userid = SessionStore::get('userid')) {
    $user = $em->find("Main:User",$userid);
    if ($user->settings->default_character) {
        $user->character = $user->settings->default_character;
    }

    Registry::setUser($user);
}

// Page preparation
$config = Registry::getMainConfig();

//--
$isPublic     = false;
$routeRequest = substr($_SERVER['REQUEST_URI'], strlen(RequestManager::getWebBasePath()));

foreach ($config->get("publicpages") as $publicpage) {
    if (substr($routeRequest, 0, strlen($publicpage)) == $publicpage) {
        $isPublic = true;
    }
}

if ($isPublic) {
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
        $page->nav->redirect("Page/Common/LogoutPage");
    } else {
        // Create the Page
        $page->create();

        // Update lastpagehit
        $user->character->lastpagehit = new DateTime();

        // Set current_nav if this is not the portal

        if (strlen($page->url) && strpos($page->url, "Page/Common/PortalPage") === false) {
            $user->character->current_nav = (string)$page->url;
        } elseif (!$user->character->current_nav || !$page->url) {
            $user->character->current_nav = "Page/Ironlance/CitysquarePage";
        }
    }
} else {
    // this is a private page, but no user is loaded. Force to logout
    SessionStore::set("logoutreason", "Automatischer Logout: Nicht eingeloggt!");
    $page = new Page();
    $page->nav->redirect("Page/Common/LogoutPage");
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

// Add Page to Registry
Registry::set('main.output', $page);
?>
