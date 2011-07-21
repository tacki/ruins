<?php
/**
* Interface for Page Objects
* @author Markus Schlegel <g42@gmx.net>
* @copyright Copyright (C) 2009 Markus Schlegel
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @package Ruins
*/

/**
 * Namespaces
 */
namespace Ruins\Common\Interfaces;
use Ruins\Common\Interfaces\OutputObjectInterface;

/**
 * Interface for Page Objects
 * @package Ruins
 */
interface PageObjectInterface
{
    /**
     * Set the Title of this Page Object
     * @param string $title
     */
    public function setTitle($title);

    /**
     * Create Content of this Page Object
     * @param OutputObjectInterface $page
     * @param array $queryParameters
     */
    public function createContent($page, $queryParameters);

    /**
     * Render the Content of this Object and display it
     */
    public function render();

    /**
     * Load the Cache and display it
     * @return bool true if successful, else false
     */
    public function renderFromCache();
}