<?php
/**
 * Dated Stack Object Class
 *
 * Class to handle stacking Objects (for example Lists..) and assign a Creationdate to this Objects
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: datedstackobject.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Stack Object Class
 *
 * Class to handle stacking Objects (for example Lists..) and assign a Creationdate to this Objects
 * @package Ruins
 */
class DatedStackObject extends StackObject
{
    /**
     * Datestack
     * @var array
     */
    protected $datestack;

    /**
     * constructor - load the default values and initialize the attributes
     * @param int $maxelements Max. Size of the Stack
     */
    function __construct($maxelements=false)
    {
        $this->datestack = array();

        parent::__construct($maxelements);
    }

    /**
     * Add a Element to the End of the Stack
     * @param mixed $listelement Element to add
     * @return int Size of the StackObject
     */
    public function add($listelement)
    {
        array_push($this->datestack, time());

        $this->_trimDateStack();

        return parent::add($listelement);
    }

    /**
     * Retrieve the first Element of the Stack
     * @param string $what What to retrieve (date|data|all)
     * @return array The first Element
     */
    public function getFirst($what = "all")
    {
        $result = array();

        switch ($what) {
            case "date":
                $result = reset($this->datestack);
                reset($this->stack);
                break;

            case "data":
                reset($this->datestack);
                $result = reset($this->stack);
                break;

            case "all":
                $result['date'] = reset($this->datestack);
                $result['data']	= reset($this->stack);
                break;
        }

        return $result;
    }

    /**
     * Retrieves the last Element of the Stack
     * @param string $what What to retrieve (date|data|all)
     * @return mixed The last Element
     */
    public function getLast($what = "all")
    {
        $result = array();

        switch ($what) {
            case "date":
                $result = end($this->datestack);
                end($this->stack);
                break;

            case "data":
                end($this->datestack);
                $result = end($this->stack);
                break;

            case "all":
                $result['date'] = end($this->datestack);
                $result['data']	= end($this->stack);
                break;
        }

        return $result;
    }

    /**
     * Retrieve the previous Element of the Stack
     * @param string $what What to retrieve (date|data|all)
     * @return mixed The previous Element
     */
    public function getPrev($what = "all")
    {
        $result = array();

        switch ($what) {
            case "date":
                $result = prev($this->datestack);
                prev($this->stack);
                break;

            case "data":
                prev($this->datestack);
                $result = prev($this->stack);
                break;

            case "all":
                $result['date'] = prev($this->datestack);
                $result['data']	= prev($this->stack);
                break;
        }

        return $result;
    }

    /**
     * Retrieve the next Element of the Stack
     * @param string $what What to retrieve (date|data|all)
     * @return mixed The next Element
     */
    public function getNext($what = "all")
    {
        $result = array();

        switch ($what) {
            case "date":
                $result = next($this->datestack);
                next($this->stack);
                break;

            case "data":
                next($this->datestack);
                $result = next($this->stack);
                break;

            case "all":
                $result['date'] = next($this->datestack);
                $result['data']	= next($this->stack);
                break;
        }

        return $result;
    }

    /**
     * Removes the first Element of the Stack
     * @return mixed The removed Element
     */
    public function delFirst()
    {
        $result = array();

        $result['date']	= array_shift($this->datestack);
        $result['data']	= array_shift($this->stack);

        return $result;
    }

    /**
     * Removes the last Element of the Stack
     * @return mixed The removed Element
     */
    public function delLast()
    {
        $result = array();

        $result['date']	= array_pop($this->datestack);
        $result['data']	= array_pop($this->stack);

        return $result;
    }

    /**
     * Export the whole Stack as an array
     * @param string $what What to export (date|data|all)
     * @return array The Stack itself
     */
    public function export($what = "all")
    {
        $result = array();

        switch ($what) {
            case "date":
                $result = $this->datestack;
                break;

            case "data":
                $result = $this->stack;
                break;

            default:
            case "all":
                $elementcount = $this->count();

                for ($i=0; $i<$elementcount; $i++) {
                    $result[$i]['date'] 	= $this->datestack[$i];
                    $result[$i]['data']	= $this->stack[$i];
                }
                break;
        }

        return $result;
    }

    /**
     * Import a List and overwrite the current Stack with it
     * @param mixed $list StackObject or Array are copied 1:1, rest is added as 1st element
     * @return int Size of the new Stack
     */
    public function import($list)
    {
        if ($list instanceof DatedStackObject) {
            $this->datestack 	= $list->export("date");
            $this->stack 		= $list->export("data");
        } elseif (is_array($list)) {
            // keep datestack in sync
            $this->datestack 	= array_fill(0, count($list), false);
            $this->stack 		= $list;
        } else {
            $this->clear();
            $this->add($list);
        }

        $this->_trimDateStack();
        $this->_trimStack();

        return $this->count();
    }

    /**
     * Clear the whole Datestack
     */
    public function clear()
    {
        $this->datestack = array();

        parent::clear();
    }

    /**
     * Trim the Stack to ensure that it won't exceed $maxsize
     */
    protected function _trimDateStack()
    {
        if ($this->maxsize && $this->maxsize <= ($stacksize = $this->count())) {
             for ($i=$stacksize; $i>$this->maxsize; $i--) {
                 array_shift($this->datestack);
             }
        }
    }

}
?>
