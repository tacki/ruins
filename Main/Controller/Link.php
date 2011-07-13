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
use Common\Controller\Error,
    Main\Entities\Group;

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
    private $_groups;

    /**
     * Constructor - Loads the default values and initializes the attributes
     * @param string $url URL the Link points to, Default false for Navigation-Header
     * @param string $position Position of the Link e.g. left|top|right
     */
    function __construct($displayname, $url=false, $position="main", $description="")
    {
        $this->displayname 	= $displayname;

        if ($url instanceof URL) {
            $this->url 		= (string)$url;
        } else {
            $this->url		= $url;
        }
        $this->position 	= $position;
        $this->description	= $description;

        $this->_groups      = array();
    }

    /**
     * Set Restrictions for the Link
     * @param string $url URL the Link points to
     */
    public function setRestriction($groups)
    {
        switch (true) {
            case $groups instanceof Group:
                $this->_groups[] = $groups;
                break;

            case is_array($groups):
                $this->_groups = $groups;
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
        if (count($this->_groups)) {
            foreach ($this->_groups as $group) {
                // Check if the Character is in one of the Groups
                if ($char->groups->contains($group)) {
                    return true;
                }
            }
        } else {
            // No Restriction set
            return true;
        }

        return false;
    }
}
?>
