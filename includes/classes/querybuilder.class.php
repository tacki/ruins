<?php
/**
 * Query Builder Class
 *
 * Class to build Queries
 * Can execute the queries if a valid MDB2 Object is given.
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: querybuilder.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Query Builder Class
 *
 * Class to build Queries
 * @package Ruins
 */
class QueryBuilder
{
    /**
     * Query Data Object
     * @var array
     */
    private $_query = array();

    /**
     * Table Prefix
     * @var string
     */
    protected $_tableprefix;

    /**
     * Quote Character
     * @var string
     */
    private $_quotechar;

    /**
     * Constructor
     * @var string $tableprefix Tableprefixed that is applied
     * @var string $quotecharacter Character which is used for string-quoting
     */
    public function __construct($tableprefix="", $quotecharacter="'")
    {
        $this->init();

        $this->_tableprefix = $tableprefix;
        $this->_quotechar = $quotecharacter;
    }

    /**
     * Return the Query if Object is handled as a String
     * @return string SQL-Query
     */
    public function __toString()
    {
        return $this->_build();
    }

    /**
     * Initialize the Object
     */
    public function init()
    {
        $this->clear();
    }

    /**
     * Clear the Object
     * @param string $what Clear a specific Query
     * @return QueryBuilder This Object
     */
    public function clear($what=false)
    {
        if ($what) {
            if ($what == "data") {
                // data - set alias
                $what = "set";
            }

            if ($what !== "action" && isset($this->_query['_'.$what])) {
                $this->_query['_'.$what] = array();
            }
        } else {
            // clear all
            $this->_query['_action'] 	= "";
            $this->_query['_select']	= array();
            $this->_query['_table']		= array();
            $this->_query['_join']		= array();
            $this->_query['_where']		= array();
            $this->_query['_order']		= array();
            $this->_query['_group']		= array();
            $this->_query['_having']	= array();
            $this->_query['_set']		= array();
            $this->_query['_limit']		= array();
        }

        return $this;
    }

    /**
     * Prefix Tablenames
     * @param string|array $values Value or array of Values to prefix
     * @return string|array The same type as $values, but prefixed
     */
    public function prefix($tablenames, $simpleprefix=false)
    {
        if ($this->_tableprefix)
        {
            if (is_array($tablenames)) {
                foreach ($tablenames as &$tablename) {
                    $tablename = $this->_autoprefix($tablename, $simpleprefix);
                }
            } else {
                $tablenames = $this->_autoprefix($tablenames, $simpleprefix);
            }
        }

        return $tablenames;
    }

    /**
     * Quote String Values
     * @param string|array $values Value or array of Values to quote
     * @return string|array The same type as $values, but quoted
     */
    public function quote($values)
    {
        if (is_array($values)) {
            foreach ($values as &$value) {
                if (is_string($value)) {
                    $value = $this->_quotechar . $value . $this->_quotechar;
                }
            }
        } else {
            if (is_string($values)) {
                $values = $this->_quotechar . $values . $this->_quotechar;
            }
        }

        return $values;
    }

    /**
     * Check if this Query is a manipulating one
     * @return bool True if this Query manipulates the DB, else false
     */
    public function isManipulating()
    {
        if (empty($this->_query['_action'])) {
            // Default to not-manipulating
            return false;
        }

        switch ($this->_query['_action']) {
            default:
                return true;

            case "SELECT":
            case "SELECT DISTINCT":
                return false;
        }
    }

    /**
     * Select Query
     * @param string What to select
     * @param bool $distinct Use distinct Select
     * @return QueryBuilder This Object
     */
    public function select($columns = "*", $distinct=false)
    {
        $this->_query['_action'] = "SELECT";

        if ($distinct) {
            $this->_query['_action'] .= " DISTINCT";
        }

        // prefix columns
        $columns = $this->prefix($columns);

        if (is_array($columns)) {
            foreach ($columns as $column) {
                $this->_query['_select'][] = $column;
            }
        } else {
            $this->_query['_select'][] = $columns;
        }

        return $this;
    }

    /**
     * Delete Query
     * @return QueryBuilder This Object
     */
    public function delete()
    {
        $this->_query['_action'] = "DELETE FROM";

        return $this;
    }

    /**
     * Delete from short alias
     * @param string $tablename Name of the Table to delete from
     * @return QueryBuilder This Object
     */
    public function deletefrom($tablename)
    {
        $this->delete();
        $this->table($tablename);

        return $this;
    }

    /**
     * Update Query
     * @param $tablename Table to update (optional)
     * @return QueryBuilder This Object
     */
    public function update($tablename=false)
    {
        $this->_query['_action'] = "UPDATE";

        if ($tablename) {
            $this->table($tablename);
        }

        return $this;
    }

    /**
     * Insert Query
     * @return QueryBuilder This Object
     */
    public function insert()
    {
        $this->_query['_action'] = "INSERT INTO";

        return $this;
    }

    /**
     * Insert Into short alias
     * @param string $tablename Name of the Table to insert into
     * @return QueryBuilder This Object
     */
    public function insertinto($tablename)
    {
        $this->insert();
        $this->table($tablename);

        return $this;
    }

    /**
     * Set Table to use (Alias of $this->table())
     * @param string $tablename Name of the Table
     * @return QueryBuilder This Object
     */
    public function from($tablename)
    {
        return $this->table($tablename);
    }

    /**
     * Set Table to use
     * @param string $tablename Name of the Table
     * @return QueryBuilder This Object
     */
    public function table($tablename)
    {
        // simple prefix tablename
        $tablename = $this->prefix($tablename, true);

        $this->_query['_table'][] = $tablename;

        return $this;
    }

    /**
     * Where Condition
     * @param string $condition Where-Condition
     * @param string $connect AND|OR
     * @return QueryBuilder This Object
     */
    public function where($condition, $connect="AND")
    {
        $append = "";

        if (count($this->_query['_where']) > 0) {
            $append = $connect . " ";
        }

        // prefix condition
        $condition = $this->prefix($condition);

        $this->_query['_where'][] = $append . $condition;

        return $this;
    }

    /**
     * Set Table Join
     * @param string $tablename Name of the Table
     * @param string $condition Where-Condition
     * @param string $jointype Join-Type DEFAULT|INNER|LEFT|RIGHT|LEFT OUTER|RIGHT OUTER
     * @return QueryBuilder This Object
     */
    public function join($tablename, $condition, $jointype="DEFAULT")
    {
        switch ($jointype) {
            default:
            case "DEFAULT":
                $this->table($tablename);
                $this->where($condition);
                break;

            case "INNER":
            case "LEFT":
            case "RIGHT":
            case "LEFT OUTER":
            case "RIGHT OUTER":
                // prefix condition
                $condition = $this->prefix($condition);
                // (simple) prefix tablename
                $tablename = $this->prefix($tablename, true);

                $this->_query['_join'][] = $jointype . " JOIN " . $tablename . " ON " . $condition;
                break;
        }

        return $this;
    }

    /**
     * Set Order
     * @param string $column Name of the column to order
     * @param bool $descending Use descending order
     * @return QueryBuilder This Object
     */
    public function order($column, $descending=false)
    {
        $append = " ASC";

        if ($descending) {
            $append = " DESC";
        }

        // prefix column
        $column = $this->prefix($column);

        $this->_query['_order'][] = $column . $append;

        return $this;
    }

    /**
     * Set Group by
     * @param string $column Name of the column to group by
     * @return QueryBuilder This Object
     */
    public function group($column)
    {
        // prefix column
        $column = $this->prefix($column);

        $this->_query['_group'][] = $column;

        return $this;
    }

    /**
     * Set Having condition
     * @param string $condition Having condition
     * @return QueryBuilder This Object
     */
    public function having($condition)
    {
        // prefix condition
        $condition = $this->prefix($condition);

        $this->_query['_having'][] = $condition;

        return $this;
    }

    /**
     * Set Values for Update or Insert
     * @param array $data Data to use for update/insert
     * @return QueryBuilder This Object
     */
    public function set(array $data)
    {
        if ($this->_query['_action'] === "UPDATE") {
            foreach ($data as $column=>$value) {
                // prefix column
                $column = $this->prefix($column);

                $this->_query['_set'][] = $column . "=" . $this->quote($value);
            }
        } elseif ($this->_query['_action'] === "INSERT INTO") {
            foreach ($data as $column=>$value) {
                // prefix column
                $column = $this->prefix($column);

                $this->_query['_set'][$column] = $value;
            }
        }

        return $this;
    }

    /**
     * Alias of set()
     * @param sarray $data Data to use for update/insert
     * @return QueryBuilder This Object
     */
    public function data(array $data)
    {
        return $this->set($data);
    }

    /**
     * Build the Query
     * @return string SQL-Query
     */
    protected function _build()
    {
        $sqlstring = $this->_query['_action'];

        switch ($this->_query['_action']) {
            case "SELECT":
            case "SELECT DISTINCT":
                $sqlstring .= $this->_buildSelect();
                $sqlstring .= $this->_buildTable();
                $sqlstring .= $this->_buildJoin();
                $sqlstring .= $this->_buildWhere();
                $sqlstring .= $this->_buildGroup();
                $sqlstring .= $this->_buildHaving();
                $sqlstring .= $this->_buildOrder();
                break;

            case "DELETE FROM":
                $sqlstring .= $this->_buildTable();
                $sqlstring .= $this->_buildWhere();
                break;

            case "UPDATE":
                $sqlstring .= $this->_buildTable();
                $sqlstring .= $this->_buildSet();
                $sqlstring .= $this->_buildWhere();
                break;

            case "INSERT INTO":
                $sqlstring .= $this->_buildTable();
                $sqlstring .= $this->_buildSet();
                break;
        }

        $this->_query['string'] = $sqlstring;

        return $this->_query['string'];
    }

    /**
     * Build the Select-Part
     * @return string SQL-Query
     */
    private function _buildSelect()
    {
        $sql = "";
        if (count($this->_query['_select']) > 0) {
            $sql .= " ";
            $sql .= implode(", ", $this->_query['_select']);
        }

        return $sql;
    }

    /**
     * Build the Table-Part
     * @return string SQL-Query
     */
    private function _buildTable()
    {
        $sql = "";
        if (count($this->_query['_table']) > 0) {
            if ($this->_query['_action'] === "SELECT" || $this->_query['_action'] === "DELETE") {
                $sql .= " FROM ";
            } else {
                $sql .= " ";
            }

            $sql .= implode(", ", $this->_query['_table']);
        }

        return $sql;
    }

    /**
     * Build the Join-Part
     * @return string SQL-Query
     */
    private function _buildJoin()
    {
        $sql = "";
        if (count($this->_query['_join']) > 0) {
            $sql .= " ";
            $sql .= implode(", ", $this->_query['_join']);
        }

        return $sql;
    }

    /**
     * Build the Where-Part
     * @return string SQL-Query
     */
    private function _buildWhere()
    {
        $sql = "";
        if (count($this->_query['_where']) > 0) {
            $sql .= " WHERE ";
            $sql .= implode(" ", $this->_query['_where']);
        }

        return $sql;
    }

    /**
     * Build the Order-Part
     * @return string SQL-Query
     */
    private function _buildOrder()
    {
        $sql = "";
        if (count($this->_query['_order']) > 0) {
            $sql .= " ORDER BY ";
            $sql .= implode(", ", $this->_query['_order']);
        }

        return $sql;
    }

    /**
     * Build the Group by-Part
     * @return string SQL-Query
     */
    private function _buildGroup()
    {
        $sql = "";
        if (count($this->_query['_group']) > 0) {
            $sql = " GROUP BY ";
            $sql .= implode(", ", $this->_query['_group']);
        }

        return $sql;
    }

    /**
     * Build the Having-Part
     * @return string SQL-Query
     */
    private function _buildHaving()
    {
        $sql = "";
        if (count($this->_query['_having']) > 0) {
            $sql = " HAVING ";
            $sql .= implode(", ", $this->_query['_having']);
        }

        return $sql;
    }

    /**
     * Build the Set-Part
     * @return string SQL-Query
     */
    private function _buildSet()
    {
        $sql = "";
        if (count($this->_query['_set']) > 0) {
            if ($this->_query['_action'] === "UPDATE") {
                $sql .= " SET ";

                $sql .= implode(", ", $this->_query['_set']);
            } elseif ($this->_query['_action'] === "INSERT INTO") {
                $sql .= " (" . implode(", ", array_keys($this->_query['_set'])) . ") ";
                $sql .= " VALUES (" . implode(", ", $this->quote($this->_query['_set'])) . ") ";
            }
        }

        return $sql;
    }

    private function _autoprefix($string, $simpleprefix=false)
    {
        // Don't do anything if no prefix is set
        if (strlen($this->_tableprefix) == 0) {
            return $string;
        }

        /*
         * ADD PREFIX TO SIMPLE STRINGS (tablenames) IF $simpleprefix IS SET
         *
         * Also check if the prefix is already set
         * Allowed Chars for this Type of Strings: A-Z,a-z,0-9,_,-
         */
        if ($simpleprefix && preg_match("/^[A-Za-z0-9_-]+$/", $string)) {
            if (substr($string, 0, strlen($this->_tableprefix)) === $this->_tableprefix) {
                return $string;
            } else {
                return $this->_tableprefix . $string;
            }
        }

        /*
         * REMOVE WHITESPACES BEFORE AND AFTER A DOT (.) AND ADD THE PREFIX
         *
         * We won't alter Text inside ' ', so we have to remove this part
         * of the string, let preg_replace run, and then add the prefixes
         * to all xxx.yyy-patterns. After that, merge the Arrays back together
         * and implode with "'".
         */
        if (strpos($string, ".") === false) {
            // return immediatelly if there is no . in this string
            return $string;
        }

        $explodearray = explode("'", $string);
        $temparray = array();

        // Save every second Entry in a new Array
        for($i=0; $i<sizeof($explodearray); $i++) {
            if ($i%2 === 0) {
                $temparray = $temparray + array_slice($explodearray, $i, 1, true);
            }
        }

        // Remove whitespaces before and after a dot
        $temparray = preg_replace('/\s*\.\s*/', '.', $temparray);

        // Remove ` and " (identifier quotes)
        $temparray = str_replace('`', "", $temparray);
        $temparray = str_replace('"', "", $temparray);

        // Set Prefix
        foreach ($temparray as &$convertstring) {
            // Get all Patterns like xxx.yyy or xxx.*, but also float like 20.5
            preg_match_all('/\w+\.(\w+|\*)/', $convertstring, $matches);

            foreach ($matches[0] as $match) {
                // Check if this isn't a number (float)
                if (is_numeric($match)) {
                    continue;
                }

                // Check if prefix is already set
                if (substr($match, 0, strlen($this->_tableprefix)) === $this->_tableprefix) {
                    continue;
                }

                // Replace only the first accurance
                $convertstring = str_replace($match, $this->_tableprefix.$match, $convertstring);
            }
        }

        // Merge both arrays
        $explodearray = $temparray + $explodearray;

        // Sort keys if they are disorderd by the merge
        ksort($explodearray);

        // Put the String back together
        $string = implode("'", $explodearray);

        return $string;
    }
}

?>
