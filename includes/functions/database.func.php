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
?>
