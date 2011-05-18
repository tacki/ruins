<?php
/**
 * UniqueID Stack Class
 *
 * Class to handle UniqueID Lists
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: uniqueidstack.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * UniqueID Stack Class
 *
 * Class to handle UniqueID Lists
 * @package Ruins
 */
class UniqueIDStack extends DatedStackObject
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param int $maxelements Max. Size of the Stack
     */
    function __construct($maxelements=false)
    {
        parent::__construct($maxelements);
    }

    /**
     * Set new UniqueID
     */
    public function setNewUniqueID()
    {
        $uniqueID = $this->_generateUniqueID();

        setcookie("ruins_uniqueid", $uniqueID, strtotime("+365 days"));
        $_COOKIE['ruins_uniqueid'] = $uniqueID;

        $this->add($uniqueID);
    }

    /**
     * Generate a new UniqueID
     */
    private function _generateUniqueID()
    {
        $uniqueID = md5(microtime());

        return $uniqueID;
    }
}
?>
