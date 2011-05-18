<?php
/**
 * Class to create for database output and design
 * @author Sebastian Meyer <greatiz@arcor.de>
 * @copyright Copyright (C) 2007 Sebastian Meyer
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: table.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

class Table extends BaseHTML
{
    /**
    * attributes of the table: background, border, cellspacing, cellpadding, class ...
    * @var array
    */
    private $_tabAttributes;

    /**
    * header of the table:
    * @var array
    */
    private $_tabHeader;

    /**
    * adding rows to the table
    * @var array
    */
    private $_tabAddRows;

    /**
    * adding a list to the table
    * @var array
    */
    private $_tabAddList;

    /**
     * second classtype
     * @var string
     */
    private $_secondRow;

    /**
     * set type of selfhighlighting
     * @var boolean
     */
    private $_setHighlight;

    /**
    * for creating a design table
    * save the count of rows
    * @var int
    */
    private $_rows;

    /**
    * for creating a design table
    * save the count of columns
    * @var int
    */
    private $_cols;

    /**
    * adding contentparts of colums
    * @var array
    */
    private $_tabAddTableContent;

    /**
    * save highest colspan
    * @var int
    */
    private $_colspan;

    /**
    * finally the table
    * @var string
    */
    public $showing;

    /**
    * set type of input date
    * 1 = Input by DB, 2 = Input by QueryToolArray, 3 = DesignInput
    * @var int
    */
    private $_inputStatus;

    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($outputclass=false)
    {
        // call parent
        parent::__construct($outputclass);

        // Initialize variables
        $this->_tabAttributes = array();
        $this->_tabHeader = array();
        $this->_tabAddList = array();
        $this->_tabAddRows['datalist'] = array();
        $this->_tabAddRows['class'] = array();
        $this->_tabAddTableContent = array();
        $this->_secondRow = "";
        $this->_selfHighlight = false;
        $this->_colspan = 0;
        $this->_inputStatus = 0;
        $this->showing = "";
    }

    //*************************
    // GENERAL FUNCTIONS
    //*************************

    /**
    * Set table attributes: Border, Background, Class, Width
    * @param mixed $width String or Int "100%" or "100" pixel [optional]
    * @param int $border Wide of the Border [optional]
    * @param string $name Name of the table
    */
    public function setTabAttributes ($width=false,$border=false,$name=false)
    {
        // Adding the Attributes if the aren't FALSE
        if ($width!=false) $this->_tabAttributes['width'] = $width;
        if (isset($this->CSSclass)) $this->_tabAttributes['class'] = $this->CSSclass;
        if ($border!=false) $this->_tabAttributes['border'] = $border;
        if ($name) $this->_tabAttributes['name'] = $name;
    }	// End setTabAttributes($width,$class,$border)

    /**
     * Set a second CSS for changing rowbackgrounds
     * @param String $class From defined ClassTypes in file.css
     */
    public function setSecondRowCSS($class) {
        if (is_string($class)) {
            $this->_secondRow = $class;
        }else {
            return "SecondRowCSS falsch gesetzt!";
        }
    }

    /**
     * Set the highlight status on/off
     * @param true or false
     */
    public function setHighlightStatus($status=false) {
        if ($status==true) {
            $this->_setHighlight = true;
        }else {
            $this->_setHighlight = false;
        }
    }

    /**
    * Add the Header to the Tab
    * @param array $titles The titles for the headrow
    * @param array $colWidth Array with String or Int "100%" or "100" pixel [optional]
    * @param array $colClass colors, fontcolors, bold, center, ...like array("`19~`37`c`b",..)[optional]
    * @param String $trClass From defined ClassTypes in file.css [optional]
    */
    public function addTabHeader ($titles,$colWidth=false,$colClass=false,$trClass=false)
    {
        $this->_tabHeader['titles'] = $titles;
        if ($colWidth!=false) $this->_tabHeader['width'] = $colWidth;
        if ($colClass!=false) $this->_tabHeader['class'] = $colClass;
        if ($trClass!=false) $this->_tabHeader['trclass']= $trClass;
    } // End addTabHeader ($titles,$attributes,$colWidth,$colClass)

    //*************************
    // DIRECTDBINPUT FUNCTIONS
    //*************************

    /**
    * Adding rows from a direct dbinput like
    * @param mixed $datarow Input data
    * @param mixed $class colors, fontcolors, bold, center, ...like array("`19~`37`c`b",..)[optional]
    * @param string $trclass colors, fontcolors, bold, center, ...like array("`19~`37`c`b",..)[optional]
    */
    public function addTabRow ($datarow,$class=false,$trclass=false)
    {
        // Set flag 1 for DirectDBInput
        $this->_inputStatus = 1;
        // check if it's an array, when not then we create one
        if (!is_array($datarow) )
        {
            // local variable
            $helparray = array();
            array_push($helparray, $datarow);
            unset($datarow);
            $datarow = $helparray;
        }
        array_push($this->_tabAddRows['datalist'],$datarow);
        // check if $class is an array & not false, when not then we create one
        if (!is_array($class) && $class != false)
        {
            // local variable
            unset($helparray);
            array_push($helparray, $class);
            unset($class);
            $class = $helparray;
        }
        array_push($this->_tabAddRows['class'],$class);
        if ($trclass!=false) $this->_tabAddRows['trclass']=$trclass;
    } // End addTabRow($datarow)

    //*************************
    // QUERYTOOL FUNCTIONS
    //*************************

    /**
    * DataInput by QueryToolArray
    * @param array $datalist QueryTool->_listRes
    * @param array $class Defined ClassTypes in file.css [optional]
    * @param String $trclass Defined ClassTypes in file.css [optional]
    */
    public function addListArray ($datalist,$class=false,$trclass=false)
    {
        // Set flag 2 for QueryToolInput
        $this->_inputStatus = 2;
        // check if array or not
        if (is_array($datalist)) $this->_tabAddList['datalist'] = $datalist;
        if (is_array($class) && $class!=false) {
            $this->_tabAddList['class']= $class;
        }else if ($class!="") {
            $this->_tabAddList['class']= $class;
        }
        if ($trclass!=false) $this->_tabAddList['trclass']= $trclass;
    } // End addListArray($datalist,$trclass)

    //*************************
    // DESIGN FUNCTIONS
    //*************************

    /**
    * Set the size of the table (number of cols & rows)
    * @param int $rows number of rows
    * @param int $cols number of cols
    */

    public function addTabSize($rows,$cols)
    {
        // set the _inputStatus flag 3 for Design
        $this->_inputStatus = 3;
        if (is_int($rows) && $rows>0)
        {
            $this->_rows = $rows;
        }else{
            $this->_rows = 1;
        }
        if (is_int($cols) && $cols>0)
        {
            $this->_cols = $cols;
        }else{
            $this->_cols = 1;
        }
    }



    /**
    * Adding preference and content to a specific field
    * @param int $row the number of the column
    * @param int $col  the number of the column
    * @param mixed $content content of the field
    * @param mixed $width String/INT "25%" or "25"
    * @param string $class colors, fontcolors, bold, center, ...like array("`19~`37`c`b",..)[optional]
    * @param int $rowspan number of rows
    * @param int $colspan number of cols
    */
    public function addFieldContent($row,$col,$content,$width=false,$class=false,$rowspan=false,$colspan=false)
    {
        if (is_int($row) && $row>0)
        {
            if (is_int($col) && $col>0)
            {
                // Adding the content
                $this->_tabAddTableContent[$row][$col]['content'] = $content;
                // Adding the width if not false
                if ($width != false) $this->_tabAddTableContent[$row][$col]['width'] = $width;
                // Adding class if not false
                if ($class != false) $this->_tabAddTableContent[$row][$col]['class'] = $class;
                // Adding rowspan
                if ($rowspan != false && is_int($rowspan) && $rowspan>0)
                {
                    $this->_tabAddTableContent[$row][$col]['rowspan'] = $rowspan;
                }else{
                    $this->_tabAddTableContent[$row][$col]['rowspan'] = 1;
                }
                // Adding colspan
                if ($colspan != false && is_int($colspan) && $colspan>0)
                {
                    $this->_tabAddTableContent[$row][$col]['colspan'] = $colspan;
                }else{
                    $this->_tabAddTableContent[$row][$col]['colspan'] = 1;
                }
            }else {
                // Out of range
                throw new Error("Gewählte Feld liegt nicht in der Tabelle!");
            }
        }else {
            // Out of range
            throw new Error("Gewählte Feld liegt nicht in der Tabelle!");
        }
    }

    //*************************
    // CREATE FUNCTION
    //*************************

    /**
    * Load the table finally
    */
    public function load()
    {
        global $user;

        reset($this->_tabAttributes);

        // First Step: Creating the table
        $this->showing = "<table";
        for ($i=0;$i<count($this->_tabAttributes);$i++)
        {
            $this->showing .= " ".key($this->_tabAttributes)."='".current($this->_tabAttributes)."'";
            next($this->_tabAttributes);
        }
        $this->showing .= ">\n";


        // Second Step: Creating the Header if it set
        if (count($this->_tabHeader) > 0)
        {
            $this->showing .= "<thead>\n";
            $this->showing .= "<tr ";
            if (isset($this->_tabHeader['trclass'])) $this->showing .= "class='".$this->_tabHeader['trclass']."'";
            $this->showing .= ">\n";
                // Initialize local variable
                $tdvalue = "";
                $i=0;
                foreach ($this->_tabHeader['titles'] as $id=>$value)
                {
                    $i++;
                    // value for th
                    $tdvalue = "<th";
                    if (isset($this->_tabHeader['width'][$i])) $tdvalue.= " width='".$this->_tabHeader['width'][$i]."'";
                    if (isset($this->_tabHeader['class'][$i])) $tdvalue.= " class='".$this->_tabHeader['class'][$i]."'";
                    if (isset($id)) $tdvalue.=" id='".$id."'";
                    $tdvalue .= ">";
                    // Stick the pieces together
                    $this->showing .= $tdvalue."".$value."</th>\n";
                }
            // Finish the Headrow
            $this->showing .= "</tr>\n";
            $this->showing .= "</thead>\n";
        }

        // Third Step: Working with the Input
        $this->showing .= "<tbody>\n";

        // Check flag _inputStatus if DirectDBInput & QueryToolInput are availble
        if (count($this->_tabAddRows)>0 && count($this->_tabAddList)>0)
        {
            // if both arrays aren't 0, we have to put them in one array
            // we put them in _tabAddList['datalist']
            // and we have to set the flag _inputStatus = 2
            // 1st Step: create a local variable to work with
            $helparray['datalist'] = array();
            $helparray['class'] = array();
            // 2nd Step: put the values in the local variable
            reset($this->_tabAddRows);
            reset($this->_tabAddRows['datalist']);
            for ($i=0;$i<count($this->_tabAddRows['datalist']);$i++)
            {
                array_push($helparray['datalist'],current($this->_tabAddRows['datalist']));
                next($this->_tabAddRows);
            }
            for ($i=0;$i<count($this->_tabAddRows['class']);$i++)
            {
                array_push($helparray['class'],current($this->_tabAddRows['class']));
                next($this->_tabAddRows);
            }
            if (isset($this->_tabAddRows['trclass']) && count($this->_tabAddRows['trclass'])==1) $helparray['trclass'] = $this->_tabAddRows['trclass'];
            // 3rd Step: put the other values in the local variable
            reset($this->_tabAddList['datalist']);
            for ($i=0;$i<count($this->_tabAddList['datalist']);$i++)
            {
                array_push($helparray['datalist'],current($this->_tabAddList['datalist']));
                next($this->_tabAddList['datalist']);
            }
            if (isset($this->_tabAddList['class'])) {
                for ($i=0;$i<count($this->_tabAddList['class']);$i++)
                {
                    if (is_array($this->_tabAddList['class'])) {
                        array_push($helparray['class'],current($this->_tabAddList['class']));
                        next($this->_tabAddList['class']);
                    }else {
                        $helparray['class'] = $this->_tabAddList['class'];
                    }
                }
            }

            if (!isset($this->_tabAddList['trclass'])) {
                $this->_tabAddList['trclass'] = "";
            }

            if (count($this->_tabAddList['trclass'])==1) $helparray['trclass'] = $this->_tabAddList['trclass'];
            // 4th Step: replace the old _tabAddList['datalist'] with the new values of the local variable
            $this->_tabAddList = $helparray;
            // 5th Step: set the flag
            $this->_inputStatus = 2;
        }

        // Select the InputStatus
        switch ($this->_inputStatus)
        {
            // DB Input
            case 1:
            $this->_tabAddList['datalist'] = array();
            for ($i=0;$i<count($this->_tabAddRows['datalist']);$i++)
            {
                array_push($this->_tabAddList['datalist'],current($this->_tabAddRows['datalist']));
                next($this->_tabAddRows['datalist']);
            }
            for ($i=0;$i<count($this->_tabAddRows['class']);$i++)
            {
                array_push($this->_tabAddList['class'],current($this->_tabAddRows['class']));
                next($this->_tabAddRows['class']);
            }
            $this->_tabAddList['trclass'] = $this->_tabAddRow['trclass'];
            // QueryTool Input
            case 2:
            $i = 0;
            if (isset($this->_tabAddList['datalist'][0])) {
                $datas = count($this->_tabAddList['datalist'][0]); // local variable
            } else {
                $datas = 0;
            }
            // if secondRow is set, but not the default trclas throw error
            if ($this->_secondRow!="" && !$this->_tabAddList['trclass']){
                throw new Error("Default trclass is not set! It has to be set, to use the SecondRowCSS!");
            }
            // if secondRow isn't set, we need to fake it
            if ($this->_secondRow=="") $this->_secondRow = $this->_tabAddList['trclass'];
            // if secondRow is set, but not the default trclas throw error
            while ($i < count($this->_tabAddList['datalist']))
            {
                reset($this->_tabAddList['datalist'][$i]);

                $trclass = ""; // local variable
                // check if your name is in the table
                if ($this->_setHighlight==true){
                    $rowdata = $this->_tabAddList['datalist'][$i]; // local variable
                    for ($h=0;$h<$datas;$h++) {
                        //FIXME: correct the entry
                        if (current($rowdata)==($user->char->displayname." (".$user->char->id.")")) {
                            $trclass = "highlight";
                        }
                        next($rowdata);
                    }
                }
                if ($trclass == "") {
                    if (is_array($this->_tabAddList['trclass'])) {
                        $trclass = $this->_tabAddList['trclass'][$i];
                    }else if ($this->_tabAddList['trclass']!="") {
                        $trclass = $i%2 ? $this->_tabAddList['trclass'] : $this->_secondRow;
                    }
                }
                $this->showing .= "<tr";
                if (is_array($this->_tabAddList['trclass'])) {
                    $this->showing .= "class='".$trclass."'";
                }else if ($this->_tabAddList['trclass']!="") {
                    $this->showing .= " class='".$trclass."'";
                }else if ($trclass=="highlight") {
                    $this->showing .= " class='".$trclass."'";
                }
                $this->showing .= ">";
                for ($x=0;$x<$datas;$x++) {
                    $this->showing .= "<td";
                    if (isset($this->_tabAddList['class'][$i]) && is_array($this->_tabAddList['class'][$i])) {
                        $this->showing .= " class='".$this->_tabAddList['class'][$i][$x]."'";
                    }else if (is_array($this->_tabAddList['class']) && isset($this->_tabAddList['class'][$x])) {
                        $this->showing .= " class='".$this->_tabAddList['class'][$x]."'";
                    }else if ($this->_tabAddList['class']!="" && !$this->_tabAddList['class']){
                        $this->showing .= " class='".$this->_tabAddList['class']."'";
                    }
                    $this->showing .= ">".btCode::decode(current($this->_tabAddList['datalist'][$i]))."</td>\n";
                    next($this->_tabAddList['datalist'][$i]);
                }
                $this->showing .= "</tr>\n";
                $i++;
            }
            break;
            // Design Input
            case 3:
            // Goal: If 6 rows are given and someone add an one field column -> the column has to have a rowspan of 6
            // This part is only to create the construct of the table -> there could be a table in one part of the table
            // IMPORTANT is the way of adding the parts
            // local variables
            $pos_row = 1;
            $pos_col = 1;
            while ($pos_row <= $this->_rows)
            {
                $this->showing .= "<tr>";
                //$this->showing .= "<td>Zeile: ".$pos_row."</td>";
                for ($i=1;$pos_col<($this->_cols+1);$i++)
                {
                    //Check if the position isn't empty
                    if (isset ($this->_tabAddTableContent[$pos_row][$pos_col]) && count($this->_tabAddTableContent[$pos_row][$pos_col])>0)
                    {
                        // open tag
                        $this->showing .= "<td";
                        // Check the preferences
                        // width
                        if (isset($this->_tabAddTableContent[$pos_row][$pos_col]['width']))
                        {
                            $this->showing .= " width='".$this->_tabAddTableContent[$pos_row][$pos_col]['width']."'";
                        }

                        // class
                        if (isset($this->_tabAddTableContent[$pos_row][$pos_col]['class']))
                        {
                            $this->showing .= " class='".$this->_tabAddTableContent[$pos_row][$pos_col]['class']."'";
                        }

                        // rowspan
                        if ($this->_tabAddTableContent[$pos_row][$pos_col]['rowspan'] > 1)
                        {
                            // we have to unset all array above this position which are in the rowspan
                            // localvariable
                            $countedrows = $this->_tabAddTableContent[$pos_row][$pos_col]['rowspan'] - 1;
                            for ($x=0;$x<$countedrows;$x++)
                            {
                                unset($this->_tabAddTableContent[($pos_row+$x+1)][$pos_col]);
                            }
                        }
                        $this->showing .= " rowspan='".$this->_tabAddTableContent[$pos_row][$pos_col]['rowspan']."'";

                        // colspan
                        $pos_diff = 0;
                        if ($this->_tabAddTableContent[$pos_row][$pos_col]['colspan']>1)
                        {
                            $pos_diff = $this->_tabAddTableContent[$pos_row][$pos_col]['colspan']-1;
                        }
                        $this->showing .= " colspan='".$this->_tabAddTableContent[$pos_row][$pos_col]['colspan']."'";
                        $this->showing .= ">";
                        // Add content
                        $this->showing .= btCode::decode($this->_tabAddTableContent[$pos_row][$pos_col]['content']);
                        // close tag
                        $this->showing .= "</td>\n";
                        $pos_col += $pos_diff;
                        $pos_diff = 0;
                    }
                    $pos_col++;
                }
                $pos_col = 1;
                $this->showing .= "</tr>\n";
                $pos_row++;
            }
            break;
            default:
        }

        // Last Step: End of table
        $this->showing .= "</tbody>\n";
        $this->showing .= "</table>\n";
        $this->showing = btCode::decoderaw($this->showing);
        return $this->generateOutput($this->showing);

    } // End load();

}
?>
