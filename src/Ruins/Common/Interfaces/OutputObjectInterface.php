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
use Ruins\Main\Controller\URL;
use Ruins\Main\Controller\Nav;

/**
 * Interface for Output Objects
 * @package Ruins
 */
interface OutputObjectInterface
{
    /**
     * Create the Object and prepare for Output
     */
    public function create();

    /**
     * Output-Wrapper
     * @param string $text The Text you wish to output
     * @param string $showhtml Set true if you want to interpret HTML in $text
     */
    public function output($text, $showhtml=false);

    /**
     * Get OutputObject-Url
     * @return URL
     */
    public function getUrl();

    /**
     * Get Navigation
     * @return Nav
     */
    public function getNavigation();

    /**
     * Set Title for this Output Object (if needed)
     * @param string @value
     */
    public function setTitle($value);

    /**
     * Create all Output and send it to the User
     */
    public function show();
}
?>
