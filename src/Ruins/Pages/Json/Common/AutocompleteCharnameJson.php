<?php
/**
 * AutoComplete AJAX Helper
 *
 * This is the AJAX Interface for Autocomplete
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Json\Common;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class AutocompleteCharnameJson extends AbstractPageObject
{
    /**
     * @see Ruins\Common\Interfaces.PageObjectInterface::createContent()
     */
    public function createContent($page, $parameters)
    {
        $em = Registry::getEntityManager();

        // check the parameter
        if(isset($parameters['part']))
        {
            $qb = $em->createQueryBuilder();

            $res = $qb->select("character.name")
                      ->from("Main:Character", "character")
                      ->where("character.name LIKE ?1")->setParameter(1, $parameters['part']."%")
                      ->orderBy("character.name", "ASC")
                      ->setMaxResults(5)
                      ->getQuery()
                      ->setResultCacheLifetime(3600)
                      ->getResult();

            foreach ($res as $entry) {
                $result[] = $entry['name'];
            }

            $page->output($result);
        }
    }
}