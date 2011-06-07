<?php
/**
 * System Class
 *
 * Class to manage Cities and other Environmentals
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Manager;
use SessionStore,
    Entities\News;

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * System Class
 *
 * Class to manage Cities and other Environmentals
 * @package Ruins
 */
class System
{
    public static function addNews($title, $body, $area=false)
    {
        global $em;
        global $user;

        $newnews = new News;
        $newnews->title   = $title;
        $newnews->body    = $body;
        $newnews->author  = $user->character;

        if($area) $newnews->area = $area;

        $em->persist($newnews);
    }

    public static function getNews($area="GLOBAL", $orderDir="DESC")
    {
        $qb = getQueryBuilder();

        $qb ->select("news")
            ->from("Entities\News", "news")
            ->orderBy("news.date", $orderDir);

        if($area) $qb->where("news.area = ?1")->setParameter(1, $area);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Translate Systemname to human readable
     * @param string $name The Systemname to translate
     * @param bool $return_unknown Return 'Unknown' if the Systemname is not found or the Systemname itself
     * @return mixed The human readable form of the Systemname
     */
    public static function translate($name, $return_unknown=false)
    {
        // get HumanReadable from Systemname
        $qb = getQueryBuilder();

        $result = $qb   ->select("translate.humanreadable")
                        ->from("Entities\Translation", "translate")
                        ->where("translate.system = ?1")->setParameter(1, $name)
                        ->getQuery()
                        ->getOneOrNullResult();

        if ($result) {
            return $result;
        } else {
            if ($return_unknown) {
                return "Unbekannt";
            } else {
                return $name;
            }
        }
    }
}
?>
