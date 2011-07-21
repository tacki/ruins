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
     * @param string $container Navigation Container Name
     * @return NavigationInterface
     */
    public function getNavigation($container="main");

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
     * Disable Caching for this Output Object
     */
    public function disableCaching();

    /**
     * Check if a Cache exists for a given Template
     * @param string $template
     * @return bool
     */
    public function cacheExists($template);

    /**
     * Clear the Cache for a given Template
     * @param string $template
     */
    public function clearCache($template);

    /**
     * Get the Latest Generated HTML-Source
     * @param string $template
     * @return string HTML-Source
     */
    public function getLatestGenerated($template);

    /**
     * Create all Output and send it to the User
     * @var string $template Template Name
     */
    public function show($template);

    /**
     * Show the latest generated Source
     */
    public function showLatestGenerated($template);
}
?>
