<?php
/**
 * Userlist
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Common;
use Ruins\Main\Controller\Link;
use Ruins\Main\Manager\SystemManager;
use Ruins\Common\Controller\AbstractPageObject;

class CharlistPage extends AbstractPageObject
{
    public $title = "Spielerliste";

    public function createContent($page, $parameters)
    {
        $em = $this->getEntityManager();

        $page->getNavigation()->addHead("Navigation");
        if (isset($parameters['return'])) {
            $page->getNavigation()->addLink("Zurück", $parameters['return']);
        } else {
            $page->output("`b`g`#25This Page needs a return-Parameter! Please fix this!");
        }

        $page->getNavigation()->addHead("Spielerliste")
                  ->addLink("Aktualisieren", $page->getUrl());


        // Database Fields to get
        $fields = array(	"displayname",
                            "level",
                            "race",
                            "profession",
                            "sex",
                            "current_nav",
                            "type"
        );

        switch ($parameters['op']) {

            default:
            case "online":
                if (isset($parameters['order']) && isset($parameters['orderDir'])) {
                    $charlist = $em->getRepository("Main:Character")
                                   ->getList($fields, $parameters['order'], $parameters['orderDir'], true);
                } else {
                    // Default to: sort by name, ascending
                    $charlist = $em->getRepository("Main:Character")
                                   ->getList($fields, "name", "ASC", true);
                }
                $newURL = clone $page->getUrl();
                $newURL->setParameter("op", "all");
                $page->getNavigation()->addLink("Alle zeigen", $newURL);
                break;

            case "all":
                if (isset($parameters['order']) && isset($parameters['orderDir'])) {
                    $charlist = $em->getRepository("Main:Character")
                                   ->getList($fields, $parameters['order'], $parameters['orderDir']);
                } else {
                    // Default to: sort by name, ascending
                    $charlist = $em->getRepository("Main:Character")
                                   ->getList($fields, "name");
                }
                $newURL = clone $page->getUrl();
                $newURL->setParameter("op", "online");
                $page->getNavigation()->addLink("Spieler Online zeigen", $newURL);
                break;
        }

        foreach ($charlist as &$character) {
            $curnav						= explode("&", $character['current_nav']);
            $character['current_nav'] 	= SystemManager::translate($curnav[0], true);
            // We don't need the Character ID here
            unset ($character['id']);
        }

        // Database Fields to sort by + Headername
        $headers = array(	"name"=>"Name",
                            "level"=>"Level",
                            "race"=>"Rasse",
                            "profession"=>"Beruf",
                            "sex"=>"Geschlecht",
                            "current_nav"=>"Ort",
                            "type"=>"Typ"
        );

        $page->addTable("characterlist", true);
        $page->getTable("characterlist")->setCSS("messagelist");
        $page->getTable("characterlist")->setTabAttributes(false);
        $page->getTable("characterlist")->addTabHeader($headers);
        $page->getTable("characterlist")->addListArray($charlist, "firstrow", "firstrow");
        $page->getTable("characterlist")->setSecondRowCSS("secondrow");
        $page->getTable("characterlist")->load();

        // Add Navlinks for the clickable Headers
        foreach ($headers as $link=>$linkname) {
            $newURL = clone $page->getUrl();
            $newURL->setParameter("order", $link);
            if (isset($parameters['order']) && isset($parameters['orderDir'])
                && $parameters['order'] == $link && $parameters['orderDir'] == "ASC") {
                $newURL->setParameter("orderDir", "DESC");
            } else {
                $newURL->setParameter("orderDir", "ASC");
            }
            $page->getNavigation()->addHiddenLink($newURL);
        }

        // Make Tableheaders clickable
        $page->addJavaScriptFile("jquery.plugin.query.js");

        $page->addJavaScript("
            $(document).ready(function() {
                $('th').hover(function() {
                    document.body.style.cursor='pointer';
                });
                $('th').mouseout(function() {
                    document.body.style.cursor='default';
                });
                $('th').click(function() {
                    if ($(this).attr('id') == jQuery.query.get('order') && jQuery.query.get('orderDir') == 'ASC') {
                        location.href = Url.decode(jQuery.query.set('order', $(this).attr('id')).set('orderDir', 'DESC'));
                    } else {
                        location.href = Url.decode(jQuery.query.set('order', $(this).attr('id')).set('orderDir', 'ASC'));
                    }
                });
            });
        ");
    }
}