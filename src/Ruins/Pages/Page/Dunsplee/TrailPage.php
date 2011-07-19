<?php
/**
 * Trail to Dunsplee Forest
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Dunsplee;
use Ruins\Main\Controller\Link;
use Ruins\Common\Controller\AbstractPageObject;

class TrailPage extends AbstractPageObject
{
    public $title  = "Dunsplee Waldweg";

    public function createContent($page, $parameters)
    {
        $page->set("pagetitle", "Dunsplee Waldweg");
        $page->set("headtitle", "Dunsplee Waldweg");

        $page->nav->addHead("Navigation")
                  ->addLink("Reisen", "Page/Common/Travel?return={$page->url->short}")
                  ->addLink("Weiher", "Page/Dunsplee/Pond")
                  ->addLink("Spielerliste", "Page/Common/Charlist?return={$page->url->short}");

        $page->nav->addHead("Allgemein")
                  ->addLink("Logout", "Page/Common/Logout");

        $page->output("Du stehst auf einem kleinen Weg, kurz bevor dieser in den dichten Dunsplee Wald verschwindet.
                        `n Die Gegend hier ist nicht sehr einladend, leicht bedrohlich. Doch einen tapferen Recken
                        wie dich wird das doch nicht abschrecken, oder?`n");

        $page->output("`n`n`n");

        $page->addChat("dunsplee_trail")->show();
    }
}