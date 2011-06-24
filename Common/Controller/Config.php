<?php
/**
 * Config Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;
use Common\Controller\Error;

/**
 * Class Defines
 */
define("CONFIG_FILENAME", DIR_CONFIG."config.cfg");

/**
 * Config Class
 *
 * @package Ruins
 */
class Config
{
    /**
     * Config File content
     * @var string
     */
    private $_config;

    /**
     * Config File content backup
     * @var string JSON encoded string
     */
    private $_backup;

    /**
     * constructor - load the config and decode it
     */
    function __construct()
    {
        // load the content of the configfile
        if (!file_exists(CONFIG_FILENAME)) {
            touch(CONFIG_FILENAME);
        }

        $this->_backup 	= file_get_contents(CONFIG_FILENAME);
        $this->_config 	= json_decode($this->_backup, true);
    }

    /**
     * destructor - save the config to the file
     */
    function __destruct()
    {
        // save the content to the configfile
        $filecontent = jsonReadable(json_encode($this->_config));

        if ($filecontent != $this->_backup) {
            // only write if something has changed
            file_put_contents(CONFIG_FILENAME, $filecontent);
        }
    }

    /**
     * Get Config entry
     * @param string $settingname Name of the Config entry to retrieve
     * @param string $defaultvalue Specify a default value, if setting doesn't exist
     * @return mixed The requested entry
     * @throws Error
     */
    public function get($settingname, $defaultvalue=NULL)
    {
        if (!isset($this->_config[$settingname]) && $defaultvalue !== NULL) {
            $this->_config[$settingname] = $defaultvalue;
        } elseif (!isset($this->_config[$settingname]) && $defaultvalue === NULL) {
            throw new Error("The given settingname ($settingname) for config->get() is invalid! Please correct or define a default value.");
        }

        return $this->_config[$settingname];
    }

    /**
     * Set Config entry
     * @param string $settingname Name of the Config entry to set
     * @param mixed $value New value of the Config entry
     */
    public function set($settingname, $value)
    {
        $this->_config[$settingname] = $value;
    }

    /**
     * Set a Config Subentry
     * @param string $settingname Name of the Config entry to get
     * @param string $subsetting Name of the Subentry to get
     * @param string $defaultvalue Specify a default value, if setting doesn't exist
     * @return mixed The requested entry
     * @throws Error
     */
    public function getSub($settingname, $subsetting, $defaultvalue=NULL)
    {
        if (!isset($this->_config[$settingname][$subsetting]) && $defaultvalue !== NULL) {
            $this->_config[$settingname][$subsetting] = $defaultvalue;
        } elseif (!isset($this->_config[$settingname][$subsetting]) && $defaultvalue === NULL) {
            throw new Error("The given settingname ($settingname[$subsetting]) for config->get() is invalid! Please correct or define a default value.");
        }

        return $this->_config[$settingname][$subsetting];
    }

    /**
     * Set Config Subentry Name of the Config entry to set
     * @param string $settingname Name of the Subentry to set
     * @param string $subsetting New value of the Config entry
     * @param mixed $value
     */
    public function setSub($settingname, $subsetting, $value)
    {
        if (!isset($this->_config[$settingname])) {
            $this->_config[$settingname] = array();
        }

        $this->_config[$settingname][$subsetting] = $value;
    }

    /**
     * Add a public Page
     * @param array $pagenames Array of Pagenames
     */
    public function addPublicPage(array $pagenames)
    {
        $publicpages 	= $this->get("publicpages", array());
        // Merge with the new pagenames
        $newpublicpages	= array_merge($publicpages, $pagenames);
        // Remove double entries
        $newpublicpages	= array_unique($newpublicpages);
        $this->set("publicpages", $newpublicpages);
    }

    /**
     * Add a no-cache Page (Pages that are private but shouldn't be cached)
     * @param array $pagenames Array of Pagenames
     */
    public function addNoCachePage(array $pagenames)
    {
        $ncpages 		= $this->get("nocachepages", array());
        // Merge with the new pagenames
        $newncpages		= array_merge($ncpages, $pagenames);
        // Remove double entries
        $newncpages		= array_unique($newncpages);
        $this->set("nocachepages", $newncpages);
    }

}

?>
