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
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Page Content
 */

use Doctrine\ORM\Tools\SchemaValidator;

$validator = new SchemaValidator($em);
$errors = $validator->validateMapping();
if (count($errors)) {
    var_dump("Schemavalidation:", $errors);
    die;
} else {
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
    $metadata = $em->getMetadataFactory()->getAllMetadata();
    //$schemaTool->dropSchema($metadata);
    $schemaTool->updateSchema($metadata);

    if (!$em->find("Entities\User", 1)) {
        $install_char = new Entities\Character;
        $install_char->name = "Saevain";
        $install_char->displayname = "Saevain";
        $install_char->level = 1;
        $install_char->healthpoints = 10;
        $install_char->lifepoints = 10;
        $install_char->strength = 5;
        $install_char->dexterity = 6;
        $install_char->constitution = 7;
        $install_char->intelligence = 6;
        $install_char->charisma = 5;
        $install_char->money = 1000;
        $install_char->loggedin = false;
        $install_char->lastpagehit = new DateTime();
        $em->persist($install_char);

        $install_user = new Entities\User;
        $install_user->login = "tacki";
        $install_user->password = md5("tacki");
        $install_user->character = $install_char;
        $install_user->lastlogin = new DateTime();
        $install_user->loggedin = false;
        $em->persist($install_user);

        $install_settings = new Entities\UserSetting;
        $install_settings->userid = $install_user;
        $install_settings->default_character = $install_char;
        $em->persist($install_settings);

        $em->flush();
    }
}


// Initialize Config-Class
$config = new Config();

// Load User if in Session
if ($userid = SessionStore::get('userid')) {
    $user = new User($userid);
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
