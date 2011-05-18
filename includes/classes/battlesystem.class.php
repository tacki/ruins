<?php
/**
 * Battlesystem Class
 *
 * Class to manage Battles
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: battlesystem.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Battlesystem Class
 *
 * Class to manage Battles
 * @package Ruins
 */
class BattleSystem
{
    /**
     * Get all current Battles
     * @param bool $onlyactive Get only the active ones
     * @return array Array of Battles with all corresponding Data
     */
    public static function getBattleList($onlyactive=false)
    {
        if (!$result = SessionStore::readCache("battlelist_".$onlyactive)) {
            $dbqt = new QueryTool();

            $dbqt	->select("*")
                    ->from("battles");

            if ($onlyactive) {
                $dbqt->where("active=1");
            }

            $result = $dbqt->exec()->fetchAll();

            // cache is only valid for 1 pagecall
            SessionStore::writeCache("battlelist_".$onlyactive, $result, "page");
        }

        return $result;
    }
}
?>
