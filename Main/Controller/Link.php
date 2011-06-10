<?php
/**
 * Link Class
 *
 * Simple Class to handle links in Ruins
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: link.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Controller;
use Common\Controller\Error;

/**
 * link Class
 *
 * Simple Class to handle links in Ruins
 * @package Ruins
 */
class Link
{
    /**
     * URL the Link points to
     * @var string
     */
    public $url;

    /**
     * Displayname of the URL
     * @var string
     */
    public $displayname;

    /**
     * Position of the Link
     * @var string
     */
    public $position;

    /**
     * Description (title)
     * @var string
     */
    public $description;

    /**
     * Array of Groups allowed to access this Link
     * @var array
     */
    private $_rights;

    /**
     * Constructor - Loads the default values and initializes the attributes
     * @param string $url URL the Link points to, Default false for Navigation-Header
     * @param string $position Position of the Link e.g. left|top|right
     * @param mixed $rights Groups who are allowed to access this Link
     */
    function __construct($displayname, $url=false, $position="main", $description="", $rights=false)
    {
        $this->displayname 	= $displayname;

        if ($url instanceof URL) {
            $this->url 		= (string)$url;
        } else {
            $this->url		= $url;
        }
        $this->position 	= $position;
        $this->description	= $description;

        $this->setRestriction($rights);
    }

    /**
     * Set Restrictions for the Link
     * @param string $url URL the Link points to
     */
    public function setRestriction($rights)
    {
        switch (true) {
            case $rights === false:
                $this->_rights = array();
                break;

            case is_string($rights):
                $this->_rights[] = $rights;
                break;

            case is_array($rights):
                $this->_rights = $rights;
                break;

            default:
                throw new Error("Invalid Restrictions set!");
                break;
        }
    }

    /**
     * Check if the given Right-Object is allowed to access this Page
     * @param $char Character Object
     * @return bool true if access is granted, else false
     */
    public function isAllowedBy($char)
    {
        if (count( array_intersect($this->_rights, $char->groups->toArray()) )) {
            // Needed rights for this link are also in the rights-object
            return true;
        } elseif (!count($this->_rights)) {
            // This link doesn't need any rights (public link)
            return true;
        }

        return false;
    }
}
?>
