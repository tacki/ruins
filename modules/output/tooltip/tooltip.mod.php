<?php
/**
 * Tooltip Module
 *
 * Enables nice-looking Tooltips to Links and Images
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Nav,
    Main\Controller\Link;

/**
 * Tooltip Module
 *
 * Enables nice-looking Tooltips to Links
 * @package Ruins
 */
class Tooltip extends Output
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "Tooltip Module"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Adds a nice looking tooltip to all Links and Images with a 'title'-Attribute"; }

    /**
     * Module Initialization
     */
    public function init()
    {
        // Initialize Parent
        parent::init();

        if ($this->outputObject instanceof OutputObject) {
            $this->outputObject->addJavaScriptFile("jquery.plugin.tooltip.min.js");

            $tooltipJS		= "$(document).ready(function(){
                                    $('a[title],img[title]').tooltip({
                                        delay: 1000,
                                        showURL: false,
                                        fade: 250
                                    });
                                });";

            $this->outputObject->addJavaScript($tooltipJS);
        }
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
