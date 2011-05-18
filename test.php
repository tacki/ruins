<?php
    require_once("config/dirconf.cfg.php");
    require_once(DIR_INCLUDES."includes.inc.php");

    $baseurl = htmlpath(DIR_BASE);
    $header = "Location: http://".$_SERVER['HTTP_HOST'] . $baseurl . "?page=developer/test";
    header($header);
?>
