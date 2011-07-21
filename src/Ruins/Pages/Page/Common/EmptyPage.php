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

class EmptyPage extends AbstractPageObject
{
    /**
     * @see \Ruins\Common\Interfaces.PageObjectInterface::createContent()
     */
    public function createContent($page, $parameters)
    {
        // Dummy Page
        // Needed for Caching
        // Do not delete
    }
}
