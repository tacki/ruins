<?php
/**
 * Interface for Navigation Objects
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Interfaces;
use Ruins\Common\Interfaces\GroupInterface;
use Ruins\Common\Controller\Request;

/**
 * Interface for Navigation Objects
 * @package Ruins
 */
interface NavigationInterface
{
    /**
     * Initialize Navigation with Request Object
     * @param Request $request
     */
    public function __construct(Request $request);

    /**
     * Get Navigation Links as array
     * @return array
     */
    public function getLinkList();

    /**
     * Get Navigation Links as array
     * @param array $linklist
     */
    public function setLinkList(array $linkList);

    /**
     * Set Navigation Container Name
     * @param string $container
     * @return NavigationInterface This Object
     */
    public function setContainerName($container);

    /**
     * Get Navigation Container Name
     * @return string
     */
    public function getContainerName();

    /**
     * Add Navigation Head
     * @param string $title Title
     * @param GroupInterface $restriction Group the Link is restricted to
     * @return NavigationInterface This Object
     */
    public function addHead($title, GroupInterface $restriction=null);

    /**
     * Add Navigation Link
     * @param string $name Shown Linkname
     * @param string $url URL
     * @param string $description Link Description
     * @param GroupInterface $restriction Group the Link is restricted to
     * @return NavigationInterface This Object
     */
    public function addLink($name, $url, $description="", GroupInterface $restriction=null);

    /**
     * Add a hidden Link to allow HTML-Forms in protected Areas
     * @param string $url URL
     * @param GroupInterface $restriction Group the Link is restricted to
     * @return NavigationInterface This Object
     */
    public function addHiddenLink($url, GroupInterface $restriction=null);

    /**
     * Add Navigation Link inside a Text
     * @param string $text Shown linked Text
     * @param string $url URL
     * @param string $description Link Description
     * @param Group $restriction Group the Link is restricted to
     * @return NavigationInterface This Object
     */
    public function addTextLink($text, $url, $description="", GroupInterface $restriction=null);
}
?>