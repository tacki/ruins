<?php
/**
 * Update Settings
 *
 * Change User/Character Settings through Ajax
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Common\Controller\SessionStore;

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_BASE."main.inc.php");

global $em;

$validator = new \Doctrine\ORM\Tools\SchemaValidator($em);
$validator->validateMapping();

global $em;

// Initialize User-Object
if ($userid = SessionStore::get('userid')) {
    $qb = $em->createQueryBuilder();

    $userconfig = $qb   ->select("settings")
                        ->from("Main:UserSetting", "settings")
                        ->where("settings.user = ?1")->setParameter(1, $userid)
                        ->getQuery()
                        ->getOneOrNullResult();
}

if (!$userid || !$userconfig || !isset($_POST['settingsobject'])) {
    echo json_encode(false);
    exit;
}

if ($_POST['settingsobject'] === 'user') {
    if (isset($_POST['arrayaction'])) {
        $arraySetting = $userconfig->$_POST['setting'];

        switch ($_POST['arrayaction']) {
            case "add":
                if (array_search($_POST['data'], $arraySetting) === false) {
                    array_push($arraySetting, $_POST['data']);
                }
                break;
            case "remove":
                if (($pos = array_search($_POST['data'], $arraySetting)) !== false) {
                    unset($arraySetting[$pos]);
                }
                break;
        }

        $userconfig->$_POST['setting'] = $arraySetting;
    } else {
        $userconfig->$_POST['setting'] = $_POST['data'];
    }
} else {
    echo json_encode(false);
    exit;
}

$em->flush();

echo json_encode(true);

?>
