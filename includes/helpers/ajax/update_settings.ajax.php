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
$validator = new \Doctrine\ORM\Tools\SchemaValidator($em);
$validator->validateMapping();

// Initialize User-Object
if ($userid = SessionStore::get('userid')) {
    $user = new User($userid);
}

if (!$userid || !$user || !isset($_POST['settingsobject'])) {
    echo json_encode(false);
    exit;
}

if ($_POST['settingsobject'] === 'user') {
    if (isset($_POST['arrayaction'])) {
        $arraySetting = $user->settings->$_POST['setting'];
        echo $arraySetting;
        switch ($_POST['arrayaction']) {
            case "add":
                array_push($arraySetting, $_POST['data']);
                break;
            case "remove":
                if ($pos=array_search($_POST['data'], $arraySetting)) {
                    unset($arraySetting[$pos]);
                }
                break;
        }
    } else {
        $user->settings->$_POST['setting'] = $_POST['data'];
    }
} else {
    echo json_encode(false);
    exit;
}

echo json_encode(true);
exit;

?>
