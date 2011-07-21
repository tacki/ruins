<?php
/**
 * Clear SessionStore-Cache
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Json\Common;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class PruneCacheJson extends AbstractPageObject
{
    /**
     * @see Ruins\Common\Interfaces.PageObjectInterface::createContent()
     */
    public function createContent($page, $parameters)
    {
        Registry::get('main.cache')->deleteAll();
    }
}