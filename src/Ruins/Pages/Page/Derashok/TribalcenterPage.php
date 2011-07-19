<?php
/**
 * Derashok Tribal Center
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Derashok;
use Ruins\Main\Controller\Link;
use Ruins\Common\Controller\AbstractPageObject;

class TribalcenterPage extends AbstractPageObject
{
    public $title  = "Derashok Stammeszentrum";

    public function createContent($page, $parameters)
    {
        $page->nav->addHead("Navigation")
                  ->addLink("Reisen", "Page/Common/Travel?return={$page->url->short}")
                  ->addLink("Thagigdash Bogoob", "Page/Derashok/Bogoob")
                  ->addLink("Spielerliste", "Page/Common/Charlist?return={$page->url->short}")
                  ->addLink("Kampfarena", "Page/Derashok/Arena");

        $page->nav->addHead("Allgemein")
                  ->addLink("Logout", "Page/Common/Logout");

        $page->output("Willkommen auf dem Stammeszentrum in Derashok, einem wichtigen Treffpunkt aller orkischen Clans.`n");

        $page->output("`n`n");

        $page->addChat("derashok_tribalcenter")->show();
    }
}