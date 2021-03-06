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
namespace Ruins\Modules\ToolTip;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Main\Controller\Link;
use Ruins\Modules\ModuleBase;
use Ruins\Common\Interfaces\ModuleInterface;

/**
 * ToolTip Module
 *
 * Show Linkdescription as nice Tooltip
 * @package Ruins
 */
class ToolTip extends ModuleBase implements ModuleInterface
{
    /**
     * @see Ruins\Common\Interfaces.Module::getName()
     */
    public function getName() { return "ToolTip Module"; }

    /**
     * @see Ruins\Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Show Linkdescription as nice Tooltip"; }

    /**
     * @see Ruins\Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(OutputObjectInterface $page)
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