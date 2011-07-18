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

    public function createContent($page, $parameters)
    {
        $page->nav->addHead("Navigation")
                  ->addLink("Reisen", "Page/Common/TravelPage?return={$page->url->short}")
                  ->addLink("Stadtbank", "Page/Ironlance/CitybankPage")
                  ->addLink("Spielerliste", "Page/Common/CharlistPage?return={$page->url->short}")
                  ->addLink("Ausrüstung", "Page/Common/EquipmentPage?return={$page->url->short}");

        $page->nav->addHead("Allgemein")
                  ->addLink("Logout", "Page/Common/LogoutPage");

        $page->output("Willkommen im Stadtzentrum von Ironlance, dem aus Stein gebauten Stolz der menschlichen Rasse.`n");

        $page->addChat("ironlance_citysquare")->show();
    }
}

