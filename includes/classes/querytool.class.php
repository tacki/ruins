<?php
/**
 * Query Tool Class
 *
 * Class to execute Queries built by QueryBuilder
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: querytool.class.php 331 2011-04-23 08:11:58Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Query Tool Class
 *
 * Class to execute Queries built by QueryBuilder
 * @package Ruins
 */
class QueryTool extends QueryBuilder
{
    /**
     * Limit the Result
     * @var array
     */
    private $_limit = array();

    /**
     * Database Object
     * @var Doctrine\DBAL\Connection Doctrine DBAL Object
     */
    private $_database;

    /**
     * Constructor
     * @var Doctrine\DBAL\Connection Doctrine DBAL Object
     */
    public function __construct($database=false)
    {
        parent::__construct();

        if (!$database) {
            $database = getDBInstance();
        }

        if ($database instanceof \Doctrine\DBAL\Connection) {
            $this->_database = $database;

            $connectionParams = $this->_database->getParams();

            if ($connectionParams['prefix']) {
                $this->_tableprefix = $connectionParams['prefix'];
            }
        } else {
            throw new Error("QueryTool needs a valid Instance of Doctrine DBAL Connection to work!");
        }
    }

    /**
     * Quote Values
     * @param string|array $values Value or array of Values to quote
     * @return string|array The same type as $values, but quoted
     */
    public function quote($values)
    {
        if (is_array($values)) {
            foreach ($values as &$value) {
                $value = $this->_database->quote($value);
            }
        } else {
            $values = $this->_database->quote($values);
        }

        return $values;
    }

    /**
     * Execute the current Query
     * @return Doctrine\DBAL\Driver\PDOStatement
     */
    public function exec()
    {
        if ($this->isManipulating()) {
            $result = $this->_database->exec($this->_build());
        } else {
            $result = $this->_database->query($this->_build());
        }

        return $result;
    }

    /**
     * Fetch all results
     * @see Doctrine\DBAL\Connection::fetchAll
     */
    public function fetchAll()
    {
        return $this->_database->fetchAll($this->_build());
    }

    /**
     * Fetch all results
     * @see Doctrine\DBAL\Connection::fetchArray
     */
    public function fetchArray()
    {
        return $this->_database->fetchArray($this->_build());
    }

    /**
     * Fetch all results
     * @see Doctrine\DBAL\Connection::fetchColumn
     */
    public function fetchColumn()
    {
        return $this->_database->fetchColumn($this->_build());
    }

    /**
     * Fetch all results
     * @see Doctrine\DBAL\Connection::fetchAssoc
     */
    public function fetchAssoc()
    {
        return $this->_database->fetchAssoc($this->_build());
    }

    /**
     * Limit the Query
     * @param int $limit How many records to show
     * @param int $offset Offset to use
     * @return QueryTool This Object
     */
    public function limit($limit, $offset)
    {
        $this->_limit['count']	= $limit;
        $this->_limit['offset'] = $offset;

        return $this;
    }

    /**
     * Build the Query
     * @return string SQL-Query
     */
    protected function _build()
    {
        $sqlstring = parent::_build();

        $sqlstring = $this->_applyLimit($sqlstring);

        return $sqlstring;
    }

    /**
     * Apply Limit
     */
    private function _applyLimit($sqlstring)
    {
        if (count($this->_limit) > 0) {
            return $this->_database ->getDatabasePlatform()
                                    ->modifyLimitQuery($sqlstring, $this->_limit['count'], $this->_limit['offset']);
        } else {
            return $sqlstring;
        }
    }
}
?>
