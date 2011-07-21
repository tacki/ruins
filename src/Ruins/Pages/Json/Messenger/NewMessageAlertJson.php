<?php
/**
 * Messenger New Mail Checker
 *
 * Checks if a Character has a new Mail
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
* Namespaces
*/
namespace Ruins\Pages\Json\Messenger;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class NewMessageAlertJson extends AbstractPageObject
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

            $result = $qb->select("message.id")
                         ->from("Main:Message", "message")
                         ->where("message.receiver = ?1")->setParameter(1, $user->getCharacter())
                         ->andWhere("message.status = 0")
                         ->getQuery()->getResult();

            $page->output(count($result));
        }
    }
}