<?php
/**
 * Database Exporter
 *
 * Exports the Database to a DB-Independant XML-Format
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: export_database.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once("config/dirconf.cfg.php");
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * File to dump to
 */
define("DB_EXPORT_FILENAME", "db dump/initial.xml");

$database = getDBInstance();
$database->loadModule('Manager');

echo "Truncate battletables...<br />";
$database->truncateTable($dbconnect['prefix']."battles");
$database->truncateTable($dbconnect['prefix']."battlemessages");
$database->truncateTable($dbconnect['prefix']."battletable");

echo "Truncate Table chat...<br />";
$database->truncateTable($dbconnect['prefix']."chat");

echo "Truncate Table messages...<br />";
$database->truncateTable($dbconnect['prefix']."messages");

echo "Truncate Table messages_references...<br />";
$database->truncateTable($dbconnect['prefix']."messages_references");

echo "Truncate Table modules...<br />";
$database->truncateTable($dbconnect['prefix']."modules");

echo "Truncate Table news...<br />";
$database->truncateTable($dbconnect['prefix']."news");

echo "Truncate Table supportrequests...<br />";
$database->truncateTable($dbconnect['prefix']."supportrequests");

echo "Truncate Table timers...<br />";
$database->truncateTable($dbconnect['prefix']."timers");


echo "Exporting Database to ". DB_EXPORT_FILENAME ."...<br />";

$options = array(
    'log_line_break' => '<br>',
    'idxname_format' => '%s',
    'debug' => true,
    'quote_identifier' => true,
    'force_defaults' => true,
    'portability' => false,
    'use_transactions' => false
);

$schema = MDB2_Schema::factory($dbconnect, $options);

if (PEAR::isError($schema)) {
    $error = $schema->getMessage();
} else {
    $definition = $schema->getDefinitionFromDatabase();

    if (PEAR::isError($definition)) {
        $error = $definition->getUserInfo();
    } else {

        $dump_options = array(
          'output_mode' => 'file',
          'output' => DB_EXPORT_FILENAME,
          'end_of_line' => "\n"
        );

        $op = $schema->dumpDatabase($definition, $dump_options, MDB2_SCHEMA_DUMP_ALL);

        if (PEAR::isError($op)) {
            $error = $op->getUserInfo();
        }
    }
}

if (isset($error)) {
    echo $error;
} else {
    echo "ok. you should call <a href='install.php'>install.php</a> before proceeding!<br />";
}

$xml = file_get_contents(DB_EXPORT_FILENAME);

// remove existing prefixes
global $dbconnect;

$currentprefix = $dbconnect['prefix'];
$tablelist = $database->listTables();

foreach ($tablelist as $tablename) {
    if (substr($tablename, 0, strlen($currentprefix)) == $currentprefix) {
        // prefix is set inside the tablename
        $withoutprefix = substr($tablename, strlen($currentprefix));
        $xml = str_replace("<name>".$tablename, "<name>".$withoutprefix, $xml);
    }

}

// Replace Special Elements inside the XML
$replacearray = array(
                        // Make prefix a variable, so we can replace it during installation (thanks to flyspray-devs for this hint)
                        "<table>\n\n  <name>" => "<table>\n\n  <name><variable>db_prefix</variable>",

                        // Handle Sequences analog to the Tables
                        "<sequence>\n  <name>" => "<sequence>\n  <name><variable>db_prefix</variable>",

                        // Set default Charset to UTF-8
                        '<charset></charset>' => '<charset>UTF-8</charset>',

                        // workaround for quotes bug
                        '&quot;' => '"',

                        // translate &apos to '
                        '&apos;' => "'",

                        // empty default values might cause problems
                        "<notnull>true</notnull>\n    <default></default>" => '<notnull>true</notnull>',
                        "<default></default>\n    <notnull>true</notnull>" => '<notnull>true</notnull>',

                        // make database name variable
                        '<name>'.$dbconnect['database'].'</name>' => '<name><variable>db_name</variable></name>',

                        // Always overwrite
                        '<overwrite>false</overwrite>'	=> '<overwrite>true</overwrite>',
);

$xml = str_replace(array_keys($replacearray), array_values($replacearray), $xml);

// Workaround for incompatibilities (postgre 2 mysql)
preg_match_all('/<default>\'(.*)\'.*<\/default>/', $xml, $matches);
for ($i=0; $i<count($matches[1]); $i++) {
    // set <default> - tags
    $matches[1][$i] = "<default>" . $matches[1][$i] . "</default>";
    // remove the ticks (')
    $matches[1][$i] = str_replace("'", "", $matches[1][$i]);
}

$xml = str_replace($matches[0], $matches[1], $xml);

// Write to File
file_put_contents(DB_EXPORT_FILENAME, $xml);

?>
