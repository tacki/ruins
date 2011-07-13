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
use Common\Interfaces\OutputObjectInterface;

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
     */
    protected $_outputclass;

    /**
     * Output Buffer
     * @var string
     */
    protected $_outputBuffer;

    /**
     * Constructor - Loads the default values and initializes the attributes
     * @param mixed $outputclass Class which handles the output
     */
    public function __construct($outputclass=false)
    {
        if ($outputclass instanceof OutputObjectInterface) {
            $this->_outputclass = $outputclass;
        }

        // set default css class
        $this->setCSS(false);
    }

    /**
     * Use getHTML() if cast to string
     */
    public function __toString()
    {
        return $this->getHTML();
    }

    /**
     * Set CSS-Class for the following Element(s)
     * @param string $class
     * @return Common\Controller\BaseHTML
     */
    public function setCSS($class)
    {
        $this->CSSclass = $class;

        return $this;
    }

    /**
     * Send to outputclass if outputclass is set
     * @param string $output Output to work with
     * @return Common\Controller\BaseHTML
     */
    protected function generateOutput($output)
    {
        if (isset($this->_outputclass)) {
            $this->_outputclass->output($output, true);
        } else {
            $this->_outputBuffer .= $output;
        }

        return $this;
    }

    /**
     * Return OutputBuffer which is not sent to Page::output()
     * @return string
     */
    public function getHTML()
    {
        $result = $this->_outputBuffer;

        // Clear Buffer
        $this->_outputBuffer = "";

        return $result;
    }
}

?>
