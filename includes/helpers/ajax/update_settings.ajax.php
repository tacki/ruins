<?php
/**
 * Update Settings
 *
 * Change User/Character Settings through Ajax
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: update_settings.ajax.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_INCLUDES."includes.inc.php");

// Initialize User-Object
$user = new User;

// Load User if in Session
if ($userid = SessionStore::get('userid')) {
    $user->load($userid);
    $user->loadCharacter();
}

if (	!isset($user) ||
        !isset($user->char) ||
        !isset($_POST['setting']) ||
        !isset($_POST['data']) ||
        !isset($_POST['settingsobject'])
    ) {
    echo json_encode(false);
    exit;
}

if ($_POST['settingsobject'] === 'user') {
    $user->settings->set($_POST['setting'], $_POST['data']);
} elseif ($_POST['settingsobject'] === 'character') {
    $user->char->settings->set($_POST['setting'], $_POST['data']);
} else {
    echo json_encode(false);
    exit;
}

echo json_encode(true);
exit;

?>
