<?php
/**
 * DebugLog Class
 *
 * Class to handle Debuglogentries
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: debuglog.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * DebugLog Class
 *
 * Class to handle Debuglogentries
 * @package Ruins
 */
class DebugLog
{
    /**
     * Log Filename
     * @var string
     */
    private $_logfile;

    /**
     * Log Content
     * @var string
     */
    private $_logcontent;

    /**
     * Object where we debug
     */
    private $_object;

    /**
     * constructor - load the config and decode it
     */
    function __construct($object=false)
    {
        if ($object instanceof Character) {
            $this->_object	= $object;
            $this->_logfile = DIR_LOG."char.".$object->name.".debug.log";
        } elseif ($object instanceof User) {
            $this->_object	= $object;
            $this->_logfile = DIR_LOG."user.".$object->login.".debug.log";
        } else {
            $this->_logfile = DIR_LOG."system.debug.log";
        }

        // load the content of the configfile
        if (!file_exists($this->_logfile)) {
            touch($this->_logfile);
        }

        $this->_logcontent 	= array();
    }

    /**
     * destructor - save the config to the file
     */
    function __destruct()
    {
        // save the content to the configfile
        if (count($this->_logcontent) > 0) {
            $filecontent = implode("", $this->_logcontent);
            file_put_contents($this->_logfile, $filecontent, FILE_TEXT|FILE_APPEND);
        }
    }

    /**
     * Add a new DebugLog Entry
     * @param string $text The Text to add
     * @param string $level The Debuglevel ("none", "default", "verbose" or "veryverbose")
     * @return mixed The ID of the new DebugEntry in the Database or false if something went wrong
     */
    public function add($text, $level="default")
    {
        $log = false;

        if (isset($this->_object->debugloglevel)) {
            switch ($this->_object->debugloglevel) {

                case "none":
                    $log = false;
                    break;

                case "default":
                    if ($level == "default") {
                        $log = true;
                    }
                    break;

                case "verbose":
                    if ($level == "default" || $level == "verbose") {
                        $log = true;
                    }
                    break;

                case "veryverbose":
                    if ($level == "default" || $level == "verbose" || $level == "veryverbose") {
                        $log = true;
                    }
                    break;
            }
        } else {
            $log = true;
        }

        if ($log) {
            $debugentry = array();
            $debugentry['date']		= date("Y-m-d H:i:s");
            $debugentry['text']		= btCode::purgeTags($text);
            $debugentry['level'] 	= $level;

            $this->_logcontent[] 	= implode(" | ", $debugentry) . "\r\n";
        } else {
            return false;
        }
    }
}
?>
