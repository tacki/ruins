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
    public $title  = "Logout Page";

    public function createContent($page, $parameters)
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            $user->addDebugLog("Logout");
            if ($user->character) $user->getCharacter()->logout();
            $user->logout();
        }

        $this->redirect("Page/Common/Login");
    }
}
