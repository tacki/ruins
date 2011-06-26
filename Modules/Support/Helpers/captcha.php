<?php
/**
 * Captcha Logic
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Common\Controller\SessionStore,
    Modules\Support\Manager;

/**
 * Global Includes
 */
require_once("../../../config/dirconf.cfg.php");
require_once(DIR_BASE."main.inc.php");

$text = Manager\Support::generateRandomString(5, true, false, true);

// Write $text to SessionStore
SessionStore::set("support_captcha", $text);

header("Content-type: image/png");
$img = ImageCreateFromPNG("../View/Images/captcha.png"); // Background
$color = ImageColorAllocate($img, 0, 0, 0); // Textcolor
$font = "../View/Fonts/DejaVuSans.ttf";
$fontsize = 25; //Schriftgrsse
$angle = rand(0,5);
$t_x = rand(5,15);
$t_y = 35;
imagettftext($img, $fontsize, $angle, $t_x, $t_y, $color, $font, $text);
imagepng($img);
imagedestroy($img)
?>