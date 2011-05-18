<?php
/**
 * Database Functions
 *
 * Functions to work with the Database etc.
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id$
 * @package Ruins
 */


/**
 * Create a DB Instance and return the Object
 * @return MDB2 MDB2 Object
 */
function getDBInstance($silent=false)
{
    global $dbconnect;

    $mdb2 = MDB2::singleton($dbconnect);

    if (PEAR::isError($mdb2)) {
        if ($silent) {
            return $mdb2;
        }
        throw new Error("Can't create MDB2-Object (" . $mdb2->getUserInfo() .")", $connection->code);
    }

    $connection = $mdb2->connect();

    if (PEAR::isError($connection)) {
        if ($silent) {
            return $connection;
        }
        throw new Error("Can't connect to the Database (" . $connection->getUserInfo() . ")", $connection->code);
    }

    // we use utf-8 only
    $setcharset = $mdb2->setCharset("utf8");

    if (PEAR::isError($setcharset)) {
        throw new Error("Characterset 'utf8' is not supported by this DBMS!");
    }

    return $mdb2;
}

/**
 * Return a Database Result as an instance of a given Object (Result is imported)
 * @param Querytool $dbqt QueryTool Object to get the Data from
 * @param string $objectname Name of the Object to use the Data with (must be an Instance of DBObject)
 * @return object Instance of the Object containing the Result of the Query
 */
function dbResultAsObjects(Querytool $dbqt, $objectname)
{
    $result = array();
    $objectname = ucfirst($objectname);

    // First check that we have a valid objectname
    if (class_exists($objectname)) {
        $object = new $objectname;
        if (!($object instanceof DBObject)) {
            throw new Error("dbResultAsObjects(): Class $objectname is not an Instance of DBObject");
        }
    } else {
        throw new Error("dbResultAsObjects(): Class $objectname is unknown");
    }

    // Fetch the Result from the Querytool-Object
    if ($dbResult = $dbqt->exec()->fetchAll()) {
        foreach ($dbResult as $row) {
            $tempobject = new $objectname;
            $tempobject->import($row);
            // Fake the loading from Database
            $tempobject->load(0);
            $result[] = clone $tempobject;
        }

    } else {
        $result = array();
    }
    return $result;
}
?>
