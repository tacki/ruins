<?php
/**
* Creating formulars
* @author Sebastian Meyer <greatiz@arcor.de>
* @author Markus Schlegel <g42@gmx.net>
* @copyright Copyright (C) 2007 Sebastian Meyer
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;
use Main\Controller\Nav,
    Main\Controller\Link;

/**
 * Creating formulars
 */
class Form extends BaseHTML
{
    /**
    * beginning of the formular set name, method and target page
    * @param string $name
    * @param string $action the target page e.g. action='test.php'
    * @param string $method use GET|POST
    * @return html string
    */
    public function head($name, $action, $method='post')
    {
        // Checking the file type and the length
        $output = "<form name='".$name."' action='?".htmlspecialchars($action)."' ";

            // checking method if GET or POST
        if ($method == 'post' || $method == 'get') {
            $output .= "method='".$method."' ";
        } else {
            // return failure
            return "\$method wurde falsch gesetzt.";
        }

        $output .= ">";

        if (isset($this->_outputclass) && $this->_outputclass->nav instanceof Nav) {
            // Add Link to allowed Navs
            $this->_outputclass->nav->addHiddenLink($action);
        }


        return $this->generateOutput($output);
    }	// END head($method,$action,$name)

    /**
    * Close the formular
    * @return html string
    */
    public function close()
    {
        $output = "</form>";

        return $this->generateOutput($output);
    }

    /**
    * input text function
    * @param string $name Name of the field to request it after submitting
    * @param string $value To set a default fieldvalue
    * @param int $size Size of the field
    * @param int $maxlength Maximun length
    * @param bool $readonly Set to true to make the Field readonly
    * @return html string
    */
    public function inputText($name,$value=false,$size=20,$maxlength=50,$readonly=false)
    {
        // checking if $name is set
        if ($name != "")
        {
            $output = "<input type='text' name='".$name."' ";
            // checking $value
            if ($value !== false) $output .= "value='".$value."' ";
            // checking $size, when it's incorrect, the default value will be set
            if (is_int($size) && $size>0)
            {
                $output .= "size='".$size."' ";
            } else {
                $output .= "size='20' ";
            }
            // checking $maxlength, when it's incorrect, $size will be the default value
            if (is_int($maxlength) && $maxlength>=$size)
            {
                $output .= "maxlength='".$maxlength."' ";
            } else {
                $output .= "maxlength='".$size."' ";
            }
            // checking CSSclass
            if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";

            if ($readonly)
            {
                $output .= "readonly='readonly'";
            }

            // closing the input
            $output .= "/>";
        } else {
            // return failure
            return "\$name nicht richtig gesetzt";
        }

        return $this->generateOutput($output);
    } // END inputText($name,$value,$size,$maxlength)


    /**
    * input password function
    * @param string $name Name of the field to request it after submitting
    * @param string $value To set a default fieldvalue
    * @param int $size Size of the field
    * @param int $maxlength Maximun length
    * @return html string
    */
    public function inputPassword($name,$value=false,$size=20,$maxlength=50)
    {
        // checking if $name is set
        if ($name != "")
        {
            $output = "<input type='password' name='".$name."' ";
            // checking $value
            if ($value != false) $output .= "value='".$value."' ";
            // checking $size, when it's incorrect, the default value will be set
            if (is_int($size) && $size>0)
            {
                $output .= "size='".$size."' ";
            } else {
                $output .= "size='20' ";
            }
            // checking $maxlength, when it's incorrect, $size will be the default value
            if (is_int($maxlength) && $maxlength>=$size)
            {
                $output .= "maxlength='".$maxlength."' ";
            } else {
                $output .= "maxlength='".$size."' ";
            }
            // checking CSSclass
            if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";
            // closing the input
            $output .= "/>";
        } else {
            // return failure
            return "\$name nicht richtig gesetzt";
        }

        return $this->generateOutput($output);
    } // END inputPassword($name,$value,$size,$maxlength)


    /**
    * Creating the SubmitButton
    * @param string $value Text on the button
    * @return html string
    */
    public function submitButton($value="")
    {
        $output  = "<input type='submit' ";

        if (strlen($value)) $output .= "value='".$value."' ";
        if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";

        $output .= "/>";

        return $this->generateOutput($output);
    } // END submitButton($value)

    /**
    * Creating the ResetButton
    * @param string $value Text on the button
    * @return html string
    */
    public function resetButton($value="")
    {
        $output  = "<input type='reset' value='".$value."' ";

        if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";

        $output .= "/>";

        return $this->generateOutput($output);
    } // END resetButton($value)

    /**
    * input textarea function
    * @param string $name name of the field
    * @param string $value value of the field
    * @param int $cols number of cols
    * @param int $rows number of rows
    * @return html string
    */
    public function textArea($name,$value=false,$cols=50,$rows=10)
    {
        if ($name!="")
        {
            $output = "<textarea name='".$name."'";
            // check if $cols is int
            if (is_int($cols) && $cols>=1)
            {
                $output .= " cols='".$cols."'";
            } else {
                $output .= " cols='50'";
            }
            // check if $rows is int
            if (is_int($rows) && $rows>=1)
            {
                $output .= " rows='".$rows."'";
            } else {
                $output .= " rows='10'";
            }
            // check if CSSclass is set
            if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";

            $output .= ">";
            // check $value
            if ($value!=false)
            {
                $output .= $value;
            }
            $output .= "</textarea>";
        } else {
            return "\$name ist nicht gesetzt.";
        }

        return $this->generateOutput($output);
    } // END textarea($name,$value, $cols, $rows)

    /**
    * checkbox function
    * @param string $name name of the checkbox
    * @param string $value value of the checkbox
    * @param boolean $onclick true|false
    * @param boolean $checked true|false
    * @return html string
    */
    public function checkbox($name,$value=false,$onclick=false,$checked=false)
    {
        if ($name != "")
        {
            $output = "<input type='checkbox' name='".$name."' ";
            if ($checked) $output .= "checked='".$checked."' ";
            if ($value!==false) $output .= "value='".$value."' ";
            if ($onclick) $output .= "onclick='".$onclick."' ";
            if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";
            $output .= "/>";
        } else {
            return "\$name ist nicht gesetzt.";
        }

        return $this->generateOutput($output);
    } // END checkbox($name,$value,$onclick,$checked)

    /**
    * Beginning of the select function
    * @param string $name name of the selection
    * @param boolean $empty empty value
    * @param int $size number of possible selection
    * @return html string
    */
    function selectStart($name,$size=1)
    {
        if ($name != "")
        {
            $output = "<select name='".$name."' ";
            if (is_int($size) && $size>=1)
            {
                $output .= "size='".$size."' ";
            } else {
                $output .= "size='1' ";
            }
            if (strlen($this->CSSclass)) $output .= "class='".$this->CSSclass."' ";
            $output .= ">";
        } else {
            return "\$name ist nicht gesetz.";
        }

        return $this->generateOutput($output);
    } // END selectStart($name,$size)

    /**
    * select Options
    * @param string $name name of the field
    * @param string $value pre-defined value
    * @param boolean $selected true if preselected
    * @return html string
    */
    public function selectOption($name,$value=false,$selected=false)
    {
        if ($name != "")
        {
            if ($value===false) {
                $output = "<option value='".$name."' ";
            } else {
                $output = "<option value='".$value."' ";
            }
            if ($selected) $output .= "selected='selected'";
            $output .= ">".$name."</option>";
        } else {
            return "\$name ist nicht gesetzt,";
        }

        return $this->generateOutput($output);
    } // END selectOption($name,$value,$selected)

    /**
    * End of select function
    * @return html string
    */
    function selectEnd()
    {
        $output = "</select>";

        return $this->generateOutput($output);
    } // END selectEnd()

    /**
     * Radio Button
     * @param string $name name of the group, the radio belongs to
     * @param string $value value of the radio
     * @return string html string
     */
    public function radio($name, $value, $checked=false, $disabled=false)
    {
        if ($name != "")
        {
            $output = "<input type='radio' name='".$name."' value='".$value."'";
            if ($checked) $output .= " checked='checked'";
            if ($disabled) $output .= " disabled='disabled'";
            $output .= "/>";
        } else {
            return "\$name ist nicht gesetzt,";
        }

        return $this->generateOutput($output);
    }

    /**
    * hidden function
    * @param string $name name of the inputfield
    * @param string $value value of the inputfield
    * @return html string
    */
    public function hidden($name, $value=false)
    {
        if ($name != "")
        {
            $output = "<input type='hidden' name='".$name."' ";
            if ($value != false) $output .= "value='".$value."'";
            $output .= "/>";
        } else {
            return "\$name ist nicht gesetzt.";
        }

        return $this->generateOutput($output);
    } // END hidden($name,$value)

}	// END class FORM
?>
