<?php
/**
 * Basic HTML Class
 *
 * Baseclass for all HTML-based classes
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Sebastian Meyer
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;
use Common\Interfaces\OutputObject;

/**
 * Basic HTML Class
 *
 * Baseclass for all HTML-based classes
 * @package Ruins
 */
class BaseHTML
{
    /**
     * CSS-Class
     * @var string
     */
    protected $CSSclass;

    /**
     * Output class
     * @var Page
     * @access private
     */
    protected $_outputclass;

    /**
     * Constructor - Loads the default values and initializes the attributes
     * @param mixed $outputclass Class which handles the output
     */
    function __construct($outputclass=false)
    {
        if ($outputclass instanceof OutputObject) {
            $this->_outputclass = $outputclass;
        }

        // set default css class
        $this->setCSS(false);
    }

    /**
     * Set CSS-Class for the following Element(s)
     * @param string $class
     */
    public function setCSS($class)
    {
        $this->CSSclass = $class;
    }

    /**
     * Send to outputclass if outputclass is set
     * @param string $output Output to work with
     * @return string|bool the output-string or true if successful
     */
    protected function generateOutput($output)
    {
        if (isset($this->_outputclass)) {
            $this->_outputclass->output($output, true);
            return true;
        } else {
            return $output;
        }
    }
}

?>
