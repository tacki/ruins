<?php
/**
 * ToolTip Module
 *
 * Show Linkdescription as nice Tooltip
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Modules\ToolTip;
use Main\Controller\Page,
    Main\Controller\Link;

/**
 * ToolTip Module
 *
 * Show Linkdescription as nice Tooltip
 * @package Ruins
 */
class ToolTip extends \Modules\ModuleBase implements \Common\Interfaces\Module
{
    /**
     * @see Common\Interfaces.Module::getModuleName()
     */
    public function getModuleName() { return "ToolTip Module"; }

    /**
     * @see Common\Interfaces.Module::getModuleDescription()
     */
    public function getModuleDescription() { return "Show Linkdescription as nice Tooltip"; }

    /**
     * @see Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page)
    {
        $page->addJavaScriptFile("jquery.plugin.tooltip.min.js");

        $tooltipJS		= "$(document).ready(function(){
                                            $('a[title],img[title]').tooltip({
                                                delay: 1000,
                                                showURL: false,
                                                fade: 250
                                            });
                                        });";

        $page->addJavaScript($tooltipJS);
    }
}
?>