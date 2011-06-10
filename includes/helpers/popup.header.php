<?php
/**
 * Popup-Header
 *
 * Common Popup-Header
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Common\Controller\Error,
    Main\Controller\Config,
    Main\Controller\Popup;

/**
 * Popup Content
 */

// Initialize Config-Class
$config = new Config();

// Load User if in Session
if ($userid = SessionStore::get('userid')) {
    $user = $em->find("Main:User",$userid);
}

// Page preparation
$config->addPublicPage(array("popup/support"));

if (array_search($_GET['popup'], $config->get("publicpages")) !== false) {
    // this is a public page!
    $popup = new Popup();

    // Create the Page
    $popup->create();
} elseif (isset($user)) {
    // this is a private page and a user is loaded
    $popup = new Popup($user->character);

    // Create the Page
    $popup->create();

    // Check for Connection Timeout
    if ($user->hasConnectionTimeout()) {
        // Connection Timeout occurred
        SessionStore::set("logoutreason", "Automatischer Logout: Connection Timeout!");

        // Redirect Parent
        $popup->redirectParent("page=common/logout");

        // Close Popup
        $popup->close();
    } else {
        // Update lastpagehit
        $user->character->lastpagehit = new DateTime();
    }
} else {
    // this is a private page, but no user is loaded. Could be a normal BadNav too
    throw new Error("Error while preparing the Page! Cannot load the (needed) User for this Page! Page is not public! (BadNav!)");
}

// BtCode
$popup->addCommonCSS("btcode.css");

// Timers
$popup->addJavaScriptFile("timer.func.js");

// Global Java Functions
$popup->addJavaScriptFile("global.func.js");

// Set Servertime on Page
$popup->set("servertime", date("H:i:s"));

?>
