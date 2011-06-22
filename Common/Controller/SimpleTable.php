<?php
/**
* simple table creating like&for form.class
* @author Sebastian Meyer <greatiz@arcor.de>
* @copyright Copyright (C) 2007 Sebastian Meyer
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;

/**
 * simple table creating like&for form.class
 * @author Sebastian Meyer <greatiz@arcor.de>
 * @package Ruins
 */
class SimpleTable extends BaseHTML
{
    /**
     * Open Tags
     * @var array
     */
    private $_open;

    /**
     * Initialize SimpleTable Class
     * @param Page $page Page Object for direct output
     */
    function __construct($page = false)
    {
        parent::__construct($page);

        // Initialize Attributes
        $this->_open = array(
                                'table' => 0,
                                'tr' => 0,
                                'th' => 0,
                                'td' => 0
                            );
    }

    /**
     * beginning of the table
     * @param mixed $width of the table
     * @param int $border width of the tableborder
     * @param int $cellspacing
     * @param int $cellpading
     * @return Common\Controller\SimpleTable
     */
    public function head($width=false,$border=0,$cellspacing=0,$cellpadding=0)
    {
        $output = "<table ";
        // checking the width
        if ($width!=false && $width!="") $output .= "width='".$width."' ";
        // checking the border
        if ($border>=0) $output .= "border='".$border."' ";
        // checking the cellspacing
        if ($cellspacing>=0) $output .= "cellspacing='".$cellspacing."' ";
        // checking the cellpadding
        if ($cellpadding>=0) $output .= "cellpadding='".$cellpadding."' ";
        // checking CSSclass
        if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";
        // close the head
        $output .= ">";

        $this->_open['table']++;

        return $this->generateOutput($output);
    } // End head($width,$border,$cellspacing,$cellpadding);

    /**
     * close the table
     * @return Common\Controller\SimpleTable
     */
    public function close()
    {
        $output = "</table>";

        $this->_open['table']--;
        $this->_closeOpenTags("table");

        return $this->generateOutput($output);
    } // END close();

    /**
     * start a table row
     * @return Common\Controller\SimpleTable
     */
    public function startRow()
    {
        $output = "<tr";

        if ($this->CSSclass) $output .= " class=".$this->CSSclass;

        $output .= ">";

        $this->_closeOpenTags("tr");
        $this->_open['tr']++;
        $this->_checkNeededTags();

        return $this->generateOutput($output);
    } // END startRow();

    /**
     * close a table row
     * @return Common\Controller\SimpleTable
     */
    public function closeRow()
    {
        $output = "</tr>";

        $this->_open['tr']--;
        $this->_closeOpenTags("tr");

        return $this->generateOutput($output);
    } // END closeRow();

    /**
     * start a head field
     * @param mixed $width
     * @param int $colspan Default value $colspan=1
     * @param int $ rowspan Default value $rowspan=1
     * @return Common\Controller\SimpleTable
     */
    public function startHead($width=false,$colspan=1,$rowspan=1)
    {
        $output = "<th ";
        // checking width
        if ($width!=false && $width!="") $output .= " width='".$width."' ";
        // checking colspan
        if (is_int($colspan) && $colspan>=1) $output .= "colspan='".$colspan."' ";
        // checking rowspan
        if (is_int($rowspan) && $rowspan>=1) $output .= "rowspan='".$rowspan."' ";
        // checking CSSclass
        if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";
        // close data
        $output .= ">";

        $this->_closeOpenTags("th");
        $this->_open['th']++;
        $this->_checkNeededTags();

        return $this->generateOutput($output);

    } // END startData($width,$colspan,$rowspan);

    /**
     * close the head field
     * @return Common\Controller\SimpleTable
     */
    public function closeHead()
    {
        $output = "</th>";

        $this->_open['th']--;
        $this->_closeOpenTags("th");

        return $this->generateOutput($output);
    } // END closeData();

    /**
     * start a data field
     * @param mixed $width
     * @param int $colspan Default value $colspan=1
     * @param int $ rowspan Default value $rowspan=1
     * @return Common\Controller\SimpleTable
     */
    public function startData($width=false,$colspan=1,$rowspan=1)
    {
        $output = "<td ";
        // checking width
        if ($width!=false && $width!="") $output .= " width='".$width."' ";
        // checking colspan
        if (is_int($colspan) && $colspan>=1) $output .= "colspan='".$colspan."' ";
        // checking rowspan
        if (is_int($rowspan) && $rowspan>=1) $output .= "rowspan='".$rowspan."' ";
        // checking CSSclass
        if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";
        // close data
        $output .= ">";

        $this->_closeOpenTags("td");
        $this->_open['td']++;
        $this->_checkNeededTags();

        return $this->generateOutput($output);

    } // END startData($width,$colspan,$rowspan);

    /**
     * close the data field
     * @return Common\Controller\SimpleTable
     */
    public function closeData()
    {
        $output = "</td>";

        $this->_open['td']--;
        $this->_closeOpenTags("td");

        return $this->generateOutput($output);
    } // END closeData();

    /**
     * Open needed Tags
     */
    private function _checkNeededTags()
    {
        if (!$this->_open['table']) {
            $this->head();
        }

        if (!$this->_open['tr']) {
            $this->startRow();
        }
    }

    /**
     * Close all open Tags
     */
    private function _closeOpenTags($position)
    {
        switch ($position)
        {
            case "table":
                if ($this->_open['td'] > 0) {
                    $this->closeData();
                }

                if ($this->_open['th'] > 0) {
                    $this->closeHead();
                }

                if ($this->_open['tr'] > 0) {
                    $this->closeRow();
                }

                if ($this->_open['table'] > 0) {
                    $this->close();
                }
                break;

            case "tr":
                if ($this->_open['td'] > 0) {
                    $this->closeData();
                }

                if ($this->_open['th'] > 0) {
                    $this->closeHead();
                }

                if ($this->_open['tr'] > 0) {
                    $this->closeRow();
                }
                break;

            case "td":
                if ($this->_open['td'] > 0) {
                    $this->closeData();
                }
                break;

            case "th":
                if ($this->_open['th']) {
                    $this->closeHead();
                }

                break;
        }
    }
} // END class
?>
