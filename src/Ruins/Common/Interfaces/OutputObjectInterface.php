<?php
/**
 * Interface for Output Objects
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Interfaces;
use Ruins\Common\Interfaces\UserInterface;
use Ruins\Common\Controller\Request;

/**
 * Interface for Output Objects
 * @package Ruins
 */
interface OutputObjectInterface
{
    /**
     * Create the Object and prepare for Output
     */
    public function create(Request $request);

    /**
     * Output-Wrapper
     * @param string $text The Text you wish to output
     * @param string $showhtml Set true if you want to interpret HTML in $text
     */
    public function output($text, $showhtml=false);

    /**
     * Get OutputObject-Url
     * @return \Ruins\Common\Controller\Url
     */
    public function getUrl();

    /**
     * Get Navigation
     * @return \Ruins\Main\Controller\Nav
     */
    public function getNavigation();

    /**
     * Assign a Navigation Object
     * @param NavigationInterface $nav
     */
    public function setNavigation(NavigationInterface $nav);

    /**
     * Assign a value to a given Template-Placeholder
     * @param string $placeholder
     * @param string $value
     */
    public function assign($placeholder, $value);

    /**
     * Set Title for this Output Object (if needed)
     * @param string @value
     */
    public function setTitle($value);

    /**
     * Set this Object to private and only available for the given User
     * @param UserInterface $user
     */
    public function setPrivate(UserInterface $user);

    /**
     * Check if the OutputObject is private
     * @return bool
     */
    public function isPrivate();

    /**
     * Create all Output and send it to the User
     * @var string $template Template Name
     */
    public function show($template);
}
?>
