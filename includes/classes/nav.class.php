<?php
/**
 * Nav Class
 *
 * Navigation Class which cares about the Navigation between the Pages
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: nav.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Nav Class
 *
 * Navigation Class which cares about the Navigation between the Pages
 * @package Ruins
 */
class Nav extends BaseObject
{
    /**
     * Flag to save Navigation to cache
     * @var bool
     */
    public $cacheNavigation;

    /**
     * Character Object
     * @var Character
     */
    private $_char;

    /**
     * Output Object
     * @var OutputObject
     */
    private $_outputObject;

    /**
     * Nav Validation Enable Flag
     * @var bool
     */
    private $_validationEnabled;

    /**
     * constructor - load the default values and initialize the attributes
     * @param Character $char Character Object
     * @param OutputObject $outputobject Parent Outputobject
     */
    function __construct($char=false, $outputobject=false)
    {
        // Call Constructor of the Parent-Class
        parent::__construct();

        // Attribute Init
        $this->cacheNavigation = false;
        $this->_char = $char;

        // Default to enabled Validation
        if ($this->_char === false) {
            $this->disableValidation();
        } else {
            $this->enableValidation();
        }

        if ($outputobject instanceof OutputObject) {
            $this->_outputObject = $outputobject;
        } else {
            $this->_outputObject = false;
        }
    }

    /**
     * Add a Link to the Linklist
     * @param Link $link Linkobject to add
     * @param int $linklistid Absolute Position of the Link
     * @return bool true if successful, else false
     */
    public function add(Link $link, $linklistid=0)
    {
        // Check if the Link is valid
        if ($this->validationEnabled() && $link->url) {
            if (!validatePHPFilePath($link->url)) {
                return false;
            }
        }

        // Check if Link already exists
        if ($this->_exists($link->displayname, $link->url)) {
            return true;
        }

        if (!$this->validationEnabled()
            || (	ModuleSystem::isModule($this->_char->rightgroups)
                    && $link->isAllowedBy($this->_char->rightgroups) )) {

            $linkdescription = array(	"displayname"=>$link->displayname,
                                        "url"=>$link->url,
                                        "position"=>$link->position,
                                        "description"=>$link->description
                                    );

            if ($linklistid > 0) {
                // insert the nav at the given position
                array_splice($this->properties, $linklistid-1, 0, array($linkdescription));
            } else {
                // add the nav to the end of the array
                $this->properties[] = $linkdescription;
            }
            $this->isloaded = true;
            return true;
        } else {
        // Check if the Char is allowed to use this link
        // FIXME
        return true;
            //return false;
        }
    }

    /**
     * Add a Textlink to the Page
     * @param Link $link Linkobject to add
     * @return bool true if successful, else false
     */
    public function addTextLink(Link $link)
    {
        // Extract Displayname
        $linktext 			= $link->displayname;
        $link->displayname 	= "";
        $link->position 	= "";

        // Add the (now) simple Link
        $this->add($link, true);

        // Output Link
        if ($this->_outputObject) {
            $this->_outputObject->output("<a href='?". $link->url . "'>" . $linktext . "</a>", true);
        } else {
            throw new Error("\$this->_outputObject is not usable here, because it's not an instance of OutputObject!");
        }
    }

    /**
     * Remove a Link from the Linklist
     * @param string $entry Displayname or URL of the Link to remove
     */
    public function remove($entry)
    {
        // Run through the Properties...
        foreach ($this->properties as $linkarray) {
            if ($linkarray['displayname'] == $entry || $linkarray['url'] == $entry) {
                unset ($this->properties[$displayname]);
            }
        }
    }

    /**
     * Load the Characters allowed Navigation
     */
    public function load()
    {
        if ($this->_char === false) {
            // public navigation
            $this->properties = array();
        } elseif (is_array($this->_char->allowednavs)) {
            // existing private navigation
            $this->properties = $this->_char->allowednavs;
        } else {
            // new private navigation
            $this->properties = array();
        }

        $this->isloaded = true;
    }

    /**
     * Load the Characters allowed Navigation from Cache
     */
    public function loadFromCache()
    {
        // existing private navigation from cache
        $this->properties = $this->_char->allowednavs_cache;

        // Set loaded Flag
        $this->isloaded = true;

        // Disable cacheNavigation for the Rest of this Page
        // and save
        $this->cacheNavigation = false;
        $this->save();
    }

    /**
     * Save the Characters allowed Navigation
     */
    public function save()
    {
        if ($this->isloaded && $this->_char !== false) {
            // Only save if this Navigation is private
            $this->_char->allowednavs = $this->properties;

            if ($this->cacheNavigation) {
                $this->_char->allowednavs_cache = $this->properties;
            }
        }
    }

    /**
     * Return Request URL
     * @return string The Request URL
     */
    public function getRequestURL()
    {
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], "?") !== false) {
            return parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        } else {
            return false;
        }
    }

    /**
     * Return Referer URL
     * @var string The Referer URL
     */
    public function getRefererURL()
    {
        if (isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF'] != "") {
            return $_SERVER['HTTP_REFERER'];
        } else {
            return false;
        }
    }

    /**
     * Check if the Link is valid
     * @param string $url URL to check
     * @param bool $noclear Don't clear, just check
     * @return bool true if successful, else false
     */
    public function checkRequestURL($url=false, $noclear=false)
    {
        global $user;

        if (!$url) {
            $url = $this->getRequestURL();
        }

        if ($this->_exists(false, $url)) {
            // Add DebugLogEntry
            $user->addDebugLog("Open $url", "verbose");

            if (!$noclear) {
                // URL is valid, so we clear the old linklist
                $this->clear();
            }

            return $url;
        } else {
            return false;
        }
    }

    /**
     * Redirect to another page
     * @param string $url Target of the redirect
     */
    public function redirect($url)
    {
        global $em;

        $em->flush();
/*
        global $user, $config;

        // Set Character Object of $this->_char if it isn't already set
        // and a valid Character is loaded. This is needed to redirect
        // from a public to a private page (for example during login)
        if (!($this->_char instanceof Character) && $user->char instanceof Character) {
            $this->_char = $user->char;
        }

        // Add Link to Navigation
        $this->add(new Link("Redirection", $url));

        // Write current Navigation to Characters allowedNavs
        $this->save();

        // Save Character
        if ($this->_char instanceof Character &&  $this->_char->isloaded) {
            // Add DebugLogEntry
            $user->char->debuglog->add("Redirect to $url", "verbose");

            // Now save
            $this->_char->save();
        }

        // Save User
        if ($user instanceof User && $user->isloaded) {
            $user->save();
        }
*/
        // Check Transactions
        $database = getDBInstance();
        if ($database->isTransactionActive()) {
            // Commit Database-Changes
            $database->commit();
        }

        // Redirect
        $baseurl = htmlpath(DIR_BASE);
        if (isset($config) && $config->get("useManualRedirect", 0)) {
            echo "Forward to $url <br />";
            echo "<a href='$baseurl?" . $url ."'>Continue</a>";
            exit;
        } else {
            $header = "Location: $baseurl?" . $url;
            header($header);
            exit;
        }
    }

    /**
     * Enable Nav Validation
     */
    public function enableValidation()
    {
        $this->_validationEnabled = true;
    }

    /**
     * Disable Nav Validation
     */
    public function disableValidation()
    {
        $this->_validationEnabled = false;
    }

    /**
     * Check if Nav Validation is enabled
     * @return bool true if Validation is enabled, else false
     */
    public function validationEnabled()
    {
        return $this->_validationEnabled;
    }

    /**
     * Check if the Link is valid
     * @access private
     * @param string $url URL to check
     * @return bool true if valid, else false
     */
    private function _exists($displayname=false, $url=false)
    {
        // Run through the Properties...
        foreach ($this->properties as $linkarray) {
            if ($displayname && $url) {
                //echo "{$displayname} == {$linkarray['displayname']} && {$url} == {$linkarray['url']} ...";
                if ($displayname == $linkarray['displayname'] && $url == $linkarray['url']) {
                    //echo "<font color='green'>ok</font><br />";
                    return true;
                } else {
                    //echo "<font color='red'>not ok</font><br />";
                }
            } elseif ($displayname) {
                //echo "{$displayname} == {$linkarray['displayname']} ...";
                if ($displayname == $linkarray['displayname']) {
                    //echo "<font color='green'>ok</font><br />";
                    return true;
                } else {
                    //echo "<font color='red'>not ok</font><br />";
                }
            } elseif ($url) {
                //echo "{$url} == {$linkarray['url']} ...";
                if ($url == $linkarray['url']) {
                    //echo "<font color='green'>ok</font><br />";
                    return true;
                } else {
                    //echo "<font color='red'>not ok</font><br />";
                }
            } else {
                //echo "<font color='red'>not ok</font><br />";
                return false;
            }
        }

        return false;
    }
}
?>
