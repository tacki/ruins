<?php
/**
 * Environment Class
 *
 * Class to manage Cities and other Environmentals
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: environmentsystem.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Environment Class
 *
 * Class to manage Cities and other Environmentals
 * @package Ruins
 */
class EnvironmentSystem
{
    /**
     * Translate Systemname to human readable
     * @param string $name The Systemname to translate
     * @param bool $return_unknown Return 'Unknown' if the Systemname is not found or the Systemname itself
     * @return mixed The human readable form of the Systemname
     */
    public static function translate($name, $return_unknown=false)
    {
        // get HumanReadable from Systemname
        if (!$result = SessionStore::readCache("systranslate_".$name)) {
            $dbqt = new QueryTool();

            $result = $dbqt	->select("humanreadable")
                            ->from("systranslations")
                            ->where("system=".$dbqt->quote($name))
                            ->exec()
                            ->fetchOne("humanreadable");

            if ($result) {
                SessionStore::writeCache("systranslate_".$name, $result);
            } else {
                if ($return_unknown) {
                    return "Unbekannt";
                } else {
                    return $name;
                }
            }
        }

        return $result;

    }
}
?>
