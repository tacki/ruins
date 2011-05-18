<?php
/**
 * Query Tool Class
 *
 * Class to execute Queries built by QueryBuilder
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: querytool.class.php 326 2011-04-19 20:19:34Z tacki $
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
     * @var MDB2
     */
    private $_database;

    /**
     * Constructor
     * @var MDB2 $database MDB2 Database Object
     */
    public function __construct($database=false)
    {
        parent::__construct();

        if (!$database) {
            $database = getDBInstance();
        }

        if ($database instanceof MDB2_Driver_Common) {
            $database->setFetchMode(MDB2_FETCHMODE_ASSOC);
            $this->_database = $database;

            if ($this->_database->dsn['prefix']) {
                $this->_tableprefix = $this->_database->dsn['prefix'];
            }
        } else {
            throw new Error("QueryTool needs a valid Instance of MDB2 to work!");
        }
    }

    /**
     * Quote Values with Data from MDB2-Object
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
     * @param bool $returnerrorobject Return the Error Object on failure
     * @return MDB2_Result|MDB2_Error|false Query Result or Error Object if $returnerrorobject is set, else false
     */
    public function exec($returnerrorobject=false)
    {
        if ($this->isManipulating()) {
            $result = $this->_database->exec($this->_build());
        } else {
            $result = $this->_database->query($this->_build());
        }

        if (PEAR::isError($result)) {
            if ($returnerrorobject) {
                // return Error Object
                return $result;
            } else {
                // throw Exception
                throw new Error($result->getUserInfo());
            }
        } else {
            // return Result
            return $result;
        }
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

        $this->_applyLimit();

        return $sqlstring;
    }

    /**
     * Apply Limit
     */
    private function _applyLimit()
    {
        if (count($this->_limit) > 0) {
            $this->_database->setLimit(	$this->_limit['count'],
                                        $this->_limit['offset']);
        }
    }
}
?>
