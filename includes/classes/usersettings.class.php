<?php
/**
 * UserSettings Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id$
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * UserSettings Class
 *
 * @package Ruins
 */
class UserSettings extends SettingsHandler
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param User $user User Object
     */
    function __construct(User $user)
    {
        $tablename  = "usersettings";
        $reference 	= "userid";

        // Call Constructor of the Parent-Class
        parent::__construct($tablename, $reference, $user->id);
    }
}
?>
