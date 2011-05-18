<?php
/**
 * CharacterSettings Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: charactersettings.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * CharacterSettings Class
 *
 * @package Ruins
 */
class CharacterSettings extends SettingsHandler
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param Character Character Object
     */
    function __construct(Character $char)
    {
        $tablename  = "charactersettings";
        $reference 	= "characterid";

        // Call Constructor of the Parent-Class
        parent::__construct($tablename, $reference, $char->id);
    }
}
?>
