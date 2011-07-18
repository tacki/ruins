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
use Ruins\Common\Controller\AbstractPageObject;

class LogoutPage extends AbstractPageObject
{
    protected $pagetitle  = "Logout Page";

    public function createContent($page, $parameters)
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            $user->addDebugLog("Logout");
            if ($user->character) $user->character->logout();
            $user->logout();
        }

        $page->nav->redirect("Page/Common/LoginPage");
    }
}
