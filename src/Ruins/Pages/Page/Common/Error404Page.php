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
use Ruins\Common\Interfaces\PageObjectInterface;

class Error404Page extends AbstractPageObject
{
    public $title  = "Page not found";

    /**
     * @see \Ruins\Common\Interfaces.PageObjectInterface::createContent()
     */
    public function createContent($page, $parameters)
    {
        $page->output("`c`b`g :( `g`b`c`n");
        $page->output("`c404 - Requested Page not found!`c");

        $page->getNavigation()
             ->addHead("Aktionen")
             ->addLink("Login Page", "Page/Common/Login");
    }
}
