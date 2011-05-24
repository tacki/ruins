<?php
/**
 * btCode AJAX Helper
 *
 * This is the AJAX Interface for btCode
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: btcode.ajax.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once("../../../includes/classes/btcode.class.php");

$decodestring = rawurldecode($_POST['decodestring']);

switch ($_GET['action']) {
    case "decode":
        echo btCode::decode($decodestring);
        break;
    case "decoderaw":
        echo btCode::decodeToCSSColorClass($decodestring);
        break;
}

?>
