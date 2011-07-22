<?php
/**
 * Ironlance City Square
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Ironlance;
use Ruins\Common\Controller\AbstractPageObject;

class CitysquarePage extends AbstractPageObject
{
    public $title  = "Ironlance Stadtzentrum";

    /**
     * @see Ruins\Common\Interfaces.PageObjectInterface::createContent()
     */
    public function createContent($page, $parameters)
    {
        $page->getNavigation()
             ->addHead("Navigation")
             ->addLink("Reisen", "Page/Common/Travel?return={$page->getUrl()->getBase()}")
             ->addLink("Stadtbank", "Page/Ironlance/Citybank")
             ->addLink("Spielerliste", "Page/Common/Charlist?return={$page->getUrl()->getBase()}")
             ->addLink("Ausrüstung", "Page/Common/Equipment?return={$page->getUrl()->getBase()}");

        $page->getNavigation()
             ->addHead("Allgemein")
             ->addLink("Logout", "Page/Common/Logout");

        $page->output("Willkommen im Stadtzentrum von Ironlance, dem aus Stein gebauten Stolz der menschlichen Rasse.`n");

        $page->addChat("ironlance_citysquare")->show();
    }
}

