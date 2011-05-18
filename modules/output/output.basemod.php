<?php
/**
 * Output Module Basemod
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: output.basemod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Output Module Basemod
 *
 * @package Ruins
 */
abstract class Output extends Module
{
    /**
     * The Output Object (page or popup)
     * @var Page
     */
    public $outputObject;

    public function init()
    {
        $this->outputObject = getOutputObject();
    }

    /**
     * Call Navigation Module
     * @param Nav $nav The Navigation-Object
     */
    public function callNavModule(Nav &$nav)
    {

    }

    /**
     * Call Text Module
     * @param array $body The Content of the Page-Body
     */
    public function callTextModule(&$body)
    {

    }
}
?>
