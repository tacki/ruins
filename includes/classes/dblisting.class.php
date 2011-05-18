<?php
/**
 * Selecting and Ordering Class
 * @author Sebastian Meyer <greatiz@arcor.de>
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Sebastian Meyer
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: dblisting.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Selecting and Ordering Class
 * @package Ruins
 */
class DBListing extends MDB_QueryTool
{
    public $prefix;

    function __construct($options = array())
    {
        global $dbconnect;

        // Set Prefix
        $this->prefix = $dbconnect['prefix'];

        // MDBversion is Hardcoded (2)
        parent::__construct(getDBInstance(), $options, 2);
    }

    /**
     * @see MDB_QueryTool_Query:setTable()
     */
    public function setTable($tablename)
    {
           $tablename = $this->_setPrefix($tablename);

        $this->sequenceName = $tablename . "_seq";

        return parent::setTable($tablename);
    }

    /**
     * @see MDB_QueryTool_Query:setSelect()
     */
     public function setSelect($what = '*')
     {
         $what = $this->_setPrefix($what);

         return parent::setSelect($what);
     }

    /**
     * @see MDB_QueryTool_Query:addSelect()
     */
     public function addSelect($what = '*', $connectstring = ',')
     {
         $what = $this->_setPrefix($what);

         return parent::addSelect($what, $connectstring);
     }

     /**
      * @see MDB_QueryTool_Query:setWhere()
      */
     public function setWhere($whereCondition='')
     {
         $whereCondition = $this->_setPrefix($whereCondition);

         return parent::setWhere($whereCondition);
     }

     /**
      * @see MDB_QueryTool_Query:addWhere()
      */
     public function addWhere($where, $condition = 'AND')
     {
         $where = $this->_setPrefix($where);

         return parent::addWhere($where, $condition);
     }

     /**
      * @see MDB_QueryTool_Query:addWhereSearch()
      */
     public function addWhereSearch($column, $string, $connectString = 'AND')
     {
         $column = $this->_setPrefix($column);

         return parent::addWhereSearch($column, $string, $connectString);
     }

      /**
      * @see MDB_QueryTool_Query:setHaving()
      */
     public function setHaving($having='')
     {
         $having = $this->_setPrefix($having);

         return parent::setHaving($having);
     }

     /**
      * @see MDB_QueryTool_Query:addHaving()
      */
     public function addHaving($what = '*', $connectString = ' AND ')
     {
         $what = $this->_setPrefix($what);

         return parent::addHaving($what, $connectString);
     }

      /**
      * @see MDB_QueryTool_Query:setGroup()
      */
     public function setGroup($group='')
     {
         $group = $this->_setPrefix($group);

         return parent::setGroup($group);
     }

    /**
     * @see MDB_QueryTool_Query:setJoin()
     */
    public function setJoin($table = null, $where = null, $joinType = 'default')
    {
        $table = $this->_setPrefix($table);
        $where = $this->_setPrefix($where);

        return parent::setJoin($table, $where, $joinType);
    }

    /**
     * @see MDB_QueryTool_Query:addJoin()
     */
    public function addJoin($table, $where, $type = 'default')
    {
        $table = $this->_setPrefix($table);
        $where = $this->_setPrefix($where);

        return parent::addJoin($table, $where, $type);
    }

    /**
     * @see MDB_QueryTool_Query:setLeftJoin()
     */
    public function setLeftJoin($table = null, $where = null)
    {
        $table = $this->_setPrefix($table);
        $where = $this->_setPrefix($where);

        return parent::setLeftJoin($table, $where);
    }

    /**
     * @see MDB_QueryTool_Query:addLeftJoin()
     */
    public function addLeftJoin($table, $where, $type = 'left')
    {
        $table = $this->_setPrefix($table);
        $where = $this->_setPrefix($where);

        return parent::addLeftJoin($table, $where, $type);
    }

    /**
     * @see MDB_QueryTool_Query:setRightJoin()
     */
    public function setRightJoin($table = null, $where = null)
    {
        $table = $this->_setPrefix($table);
        $where = $this->_setPrefix($where);

        return parent::setRightJoin($table, $where);
    }

    /**
     * Set Prefix at parts of the Query-String
     * @param string $string The Query-Part
     * @param int $mode Replacement-Mode to use
     * @return The translated String (with Table-Prefix)
     */
    private function _setPrefix($string)
    {
        // Don't do anything if no prefix is set
        if (strlen($this->prefix) == 0) {
            return $string;
        }

        /*
         * ADD PREFIX TO SIMPLE STRINGS (tablenames)
         *
         * Also check if the prefix is already set
         * Allowed Chars for this Type of Strings: A-Z,a-z,0-9,_,-
         */
        if (preg_match("/^[A-Za-z0-9_-]+$/", $string)) {

            // Check if this is a table
            if (!$tables = SessionStore::readCache("tablenames")) {
                $tablelist = getDBInstance();
                $tablelist->loadModule("Manager");
                $tables = $tablelist->listTables();
                SessionStore::writeCache("tablenames", $tables);
            }

            // Check if this is a Tablename
            if (in_array($this->prefix.$string, $tables, true)) {
                // Check if prefix is already set
                if (substr($string, 0, strlen($this->prefix)) === $this->prefix) {
                    return $string;
                } else {
                    return $this->prefix . $string;
                }
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
        foreach ($temparray as $convertnr=>$convertstring) {
            // Get all Patterns like xxx.yyy, but also float like 20.5
            preg_match_all('/\w+\.\w+/', $convertstring, $matches);

            foreach ($matches[0] as $match) {
                // Check if this isn't a number (float)
                if (is_numeric($match)) {
                    continue;
                }

                // Check if prefix is already set
                if (substr($match, 0, strlen($this->prefix)) === $this->prefix) {
                    continue;
                }

                // Replace only the first accurance
                $convertstring = str_replace($match, $this->prefix.$match, $convertstring);

                // Update $temparray with the translated String
                $temparray[$convertnr] = $convertstring;
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
