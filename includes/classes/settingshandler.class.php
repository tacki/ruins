<?php
/**
 * Settings Handler Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: settingshandler.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Settings Handler Class
 *
 * @package Ruins
 */
class SettingsHandler
{
    /**
     * Table to use
     * @var string
     */
    private $_tablename;

    /**
     * Name of the Referencefield
     * @var string
     */
    private $_reference;

    /**
     * ID of the Reference
     * @var mixed
     */
    private $_referenceid;

    /**
     * constructor - load the default values and initialize the attributes
     * @param string $tablename Name of the DB-Table to use
     * @param string $reference Name of the Referencefield to check
     * @param mixed $referenceid Value of the Referencefield
     */
    function __construct($tablename, $reference, $referenceid)
    {
        $this->_tablename = $tablename;
        $this->_reference = $reference;
        $this->_referenceid = $referenceid;
    }

    /**
     * Getter Function
     * @param string $settingname Name of the setting to get
     * @return mixed Value of this setting
     */
    public function &__get($settingname)
    {
        $result = $this->get($settingname);
        return $result;
    }

    /**
     * Setter Function
     * @param string $settingname Name of the setting to get
     * @param mixed $value Value of the setting
     */
    public function __set($settingname, $value)
    {
        if ($this->get($settingname) !== false) {
            $this->set($settingname, $value);
        } else {
            $this->set($settingname, $value, true);
        }
    }

    /**
     * Get a Setting
     * @param string $settingname Name of the setting to get
     * @param mixed $defaultvalue
     * @return mixed Value of this setting
     */
    public function get($settingname, $defaultvalue=false)
    {
        if (!( $cachecontent = SessionStore::readCache($this->_getCachename()) )) {
            // Fill the Cache and return it's content
            $cachecontent = $this->_fillCache();
        }

        $result = false;

        // Search Settings
        foreach ($cachecontent as $row) {
            if (isset($row['settingname']) && $row['settingname'] == $settingname) {
                $result = $row['value'];
            }
        }

        // Assign defaultvalue if nothing is found (create)
        if ($result === false && $defaultvalue !== false) {
            $this->set($settingname, $defaultvalue, true);
            $result = $defaultvalue;
        }

        if ($result !== false) {
            $result = stripslashes($result);

            if (is_serialized($result)) {
                $result = unserialize($result);
            }
        }

        return $result;

    }

    /**
     * Return Cachename for this Object
     * @return string Cachename
     */
    private function _getCachename()
    {
        return "settings_".$this->_reference."_".$this->_referenceid;
    }

    /**
     * Fetch all Values from Database and fill the Cache
     * @return array The cache itself
     */
    private function _fillCache()
    {
        $dbqt = new QueryTool;

        $result = $dbqt	->select("settingname, value")
                        ->from($this->_tablename)
                        ->where($this->_reference . "=" . $dbqt->quote($this->_referenceid))
                        ->exec()
                        ->fetchAll();

        SessionStore::writeCache($this->_getCachename(), $result);

        return $result;
    }

    /**
     * Set a Setting
     * @param string $settingname Settingname to set
     * @param mixed $value Value to set
     */
    public function set($settingname, $value, $create=false)
    {
        $dbqt = new Querytool;

        if (is_object($value) || is_array($value)) {
            $value = addslashes(serialize($value));
        } else {
            $value = addslashes($value);
        }

        $data = array(
                        $this->_reference => $this->_referenceid,
                        "settingname" => $settingname,
                        "value" => $value
                    );

        if ($create) {
            $dbqt	->insertinto($this->_tablename)
                    ->where($this->_reference . "=" . $this->_referenceid)
                    ->set($data)
                    ->exec();

            // add to cache
            if ($cachecontent = SessionStore::readCache($this->_getCachename()) ) {
                $cachecontent[] = array ("settingname" => $settingname, "value" => $value);
                SessionStore::writeCache($this->_getCachename(), $cachecontent);
            }

        } else {
            $dbqt	->update($this->_tablename)
                    ->where($this->_reference . "=" . $this->_referenceid)
                    ->where("settingname=".$dbqt->quote($settingname))
                    ->set($data)
                    ->exec();

            // alter cache
            if ($cachecontent = SessionStore::readCache($this->_getCachename()) ) {
                foreach ($cachecontent as &$row) {
                    if ($row['settingname'] == $settingname) {
                        $row['value'] = $value;
                    }
                }

                SessionStore::writeCache($this->_getCachename(), $cachecontent);
            }
        }
    }
}
?>
