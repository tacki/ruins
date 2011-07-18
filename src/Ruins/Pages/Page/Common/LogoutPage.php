<?php
/**
 * Logout Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Common;
use Ruins\Main\Entities\User;
use Ruins\Common\Controller\Registry;
use Ruins\Main\Controller\Page;
use Ruins\Common\Interfaces\PageObjectInterface;

class LogoutPage extends Page implements PageObjectInterface
{
    protected $pagetitle  = "Logout Page";

    public function setTitle()
    {
        $this->set("pagetitle", $this->pagetitle);
        $this->set("headtitle", $this->pagetitle);
    }

    public function createMenu()
    {
    }

    public function createContent(array $parameters)
    {
        $user = Registry::getUser();

        if ($user instanceof User) {
            $user->addDebugLog("Logout");
            if ($user->character) $user->character->logout();
            $user->logout();
        }

        $this->nav->redirect("Page/Common/LoginPage");
    }
}
