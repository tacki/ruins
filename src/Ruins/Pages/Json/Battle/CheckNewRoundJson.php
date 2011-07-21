<?php
/**
 * Battleround getter
 *
 * Retrieves the Battleround for the given Char
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Json\Battle;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class CheckNewRoundJson extends AbstractPageObject
{
    /**
     * @see Ruins\Common\Interfaces.PageObjectInterface::createContent()
     */
    public function createContent($page, $parameters)
    {
        $em = Registry::getEntityManager();
        $user = Registry::getUser();

        if ($user) {
            $qb = $em->createQueryBuilder();

            $result = $qb   ->select("bt.round")
                            ->from("Main:Battle", "bt")
                            ->from("Main:BattleMember", "bm")
                            ->where("bm.battle = bt")
                            ->andWhere("bm.character = ?1")->setParameter(1, $user->getCharacter())
                            ->getQuery()->getOneOrNullResult();

            $page->output((int)$result);
        }
    }
}