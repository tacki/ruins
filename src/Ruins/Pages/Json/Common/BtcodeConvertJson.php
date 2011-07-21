<?php
/**
 * btCode AJAX Helper
 *
 * This is the AJAX Interface for btCode
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Json\Common;
use Ruins\Common\Controller\BtCode;
use Ruins\Common\Controller\AbstractPageObject;

class BtcodeConvertJson extends AbstractPageObject
{
    /**
     * @see Ruins\Common\Interfaces.PageObjectInterface::createContent()
     */
    public function createContent($page, $parameters)
    {
        $decodestring = $parameters['decodestring'];

        switch ($parameters['action']) {
            case "decode":
                $page->output(BtCode::decode($decodestring));
                break;
            case "decoderaw":
                $page->output(BtCode::decodeToCSSColorClass($decodestring));
                break;
        }
    }
}