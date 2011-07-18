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
use Ruins\Main\Entities\User;
use Ruins\Common\Controller\Registry;
use Ruins\Main\Controller\Page;
use Ruins\Common\Interfaces\PageObjectInterface;

class CitysquarePage extends Page implements PageObjectInterface
{
    protected $pagetitle  = "Ironlance Stadtzentrum";

    public function setTitle()
    {
        $this->set("pagetitle", $this->pagetitle);
        $this->set("headtitle", $this->pagetitle);
    }

    public function createMenu()
    {
        $this->nav->addHead("Navigation")
                  ->addLink("Reisen", "page/common/travel&return={$this->url->short}")
                  ->addLink("Stadtbank", "page/ironlance/citybank")
                  ->addLink("Spielerliste", "page/common/charlist&return={$this->url->short}")
                  ->addLink("Ausrüstung", "page/common/equipment&return={$this->url->short}");

        $this->nav->addHead("Allgemein")
                  ->addLink("Logout", "Page/Common/LogoutPage");
    }

    public function createContent(array $parameters)
    {
        $this->output("Willkommen im Stadtzentrum von Ironlance, dem aus Stein gebauten Stolz der menschlichen Rasse.`n");

        $this->addChat("ironlance_citysquare")->show();
    }
}

