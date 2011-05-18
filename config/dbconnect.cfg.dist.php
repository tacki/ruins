<?php
/**
 * Sample Database config
 *
 * Save this file as dbconnect.cfg.php and place it into the config-directory
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

$dbconnect = array(

    # the type of the database-engine. please ensure that you have the apropriate mdb2-driver installed
    'phptype'  => 'mysql',

    # Database user
    'username' => 'someuser',

    # Database password
    'password' => 'apasswd',

    # Database hostname
    'hostspec' => 'localhost',

    # Optional: Connect via SSL
    #'key'      => 'client-key.pem',
    #'cert'     => 'client-cert.pem',
    #'ca'       => 'cacert.pem',
    #'capath'   => '/path/to/ca/dir',
    #'cipher'   => 'AES',

    # Database name
    'database' => 'thedb',

    # Table prefix
    'prefix' => ''
);

?>
