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
 * @return \Doctrine\DBAL\Connection Doctrine DBAL Object
 */
function getDBInstance()
{
    global $dbconnect;

    $connection = \Doctrine\DBAL\DriverManager::getConnection($dbconnect);
    $connection->connect();

    return $connection;
}

/**
 * Create and return a fresh QueryBuilder
 * @return \Doctrine\ORM\EntityManager Doctrine QueryBuilder
 */
function getQueryBuilder()
{
    global $em;

    if ($em instanceof \Doctrine\ORM\EntityManager) {
        return $em->createQueryBuilder();
    }
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
