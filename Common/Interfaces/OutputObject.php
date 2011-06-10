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
namespace Common\Interfaces;

/**
 * Interface for Output Objects
 * @package Ruins
 */
interface OutputObject
{
    /**
     * Output-Wrapper
     * @param string $text The Text you wish to output
     * @param string $showhtml Set true if you want to interpret HTML in $text
     */
    public function output($text, $showhtml=false);
}
?>
