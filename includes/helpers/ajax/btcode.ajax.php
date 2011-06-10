<?php
/**
 * btCode AJAX Helper
 *
 * This is the AJAX Interface for btCode
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\BtCode;

/**
 * Includes
 */
require_once("/Main/Controller/BtCode.php");

$decodestring = rawurldecode($_POST['decodestring']);

switch ($_GET['action']) {
    case "decode":
        echo BtCode::decode($decodestring);
        break;
    case "decoderaw":
        echo BtCode::decodeToCSSColorClass($decodestring);
        break;
}

?>
