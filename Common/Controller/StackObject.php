<?php
/**
 * Stack Object Class
 *
 * Class to handle stacking Objects (for example Lists..)
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;

/**
 * Stack Object Class
 *
 * Class to handle stacking Objects (for example Lists..)
 * @package Ruins
 */
class StackObject
{
    /**
     * The Stack
     * @var array
     */
    protected $stack;

    /**
     * Maximum Size of the Stack
     */
    protected $maxsize;

    /**
     * constructor - load the default values and initialize the attributes
     * @param int $maxelements Max. Size of the Stack
     */
    function __construct($maxelements=false)
    {
        $this->stack = array();

        if (is_numeric($maxelements)) {
            $this->maxsize = $maxelements;
        } else {
            $this->maxsize = false;
        }
    }

    /**
     * Add a Element to the End of the Stack
     * @param mixed $listelement Element to add
     * @return int Size of the StackObject
     */
    public function add($listelement)
    {
        array_push($this->stack, $listelement);

        $this->_trimStack();

        return $this->count();
    }

    /**
     * Retrieve the first Element of the Stack
     * @return mixed The first Element
     */
    public function getFirst()
    {
        return reset($this->stack);
    }

    /**
     * Retrieves the last Element of the Stack
     * @return mixed The last Element
     */
    public function getLast()
    {
        return end($this->stack);
    }

    /**
     * Retrieve the previous Element of the Stack
     * @return mixed The previous Element
     */
    public function getPrev()
    {
        return prev($this->stack);
    }

    /**
     * Retrieve the next Element of the Stack
     * @return mixed The next Element
     */
    public function getNext()
    {
        return next($this->stack);
    }

    /**
     * Removes the first Element of the Stack
     * @return mixed The removed Element
     */
    public function delFirst()
    {
        return array_shift($this->stack);
    }

    /**
     * Removes the last Element of the Stack
     * @return mixed The removed Element
     */
    public function delLast()
    {
        return array_pop($this->stack);
    }

    /**
     * Export the whole Stack as an array
     * @return array The Stack itself
     */
    public function export()
    {
        return $this->stack;
    }

    /**
     * Import a List and overwrite the current Stack with it
     * @param mixed $list StackObject or Array are copied 1:1, rest is added as 1st element
     * @return int Size of the new Stack
     */
    public function import($list)
    {
        if ($list instanceof StackObject) {
            $this->stack = $list->export();
        } elseif (is_array($list)) {
            $this->stack = $list;
        } else {
            $this->clear();
            $this->add($list);
        }

        $this->_trimStack();

        return $this->count();
    }

    /**
     * Get the Size of the Stack
     * @return int Size of the Stack
     */
    public function count()
    {
        return count($this->stack);
    }

    /**
     * Check if the given Element is inside the Stack
     * @var mixed $searchelement Element to search
     * @return bool True if the Element is inside the Stack, else false
     */
    public function contains($searchelement)
    {
        return in_array($searchelement, $this->stack);
    }

    /**
     * Clear the whole Stack
     */
    public function clear()
    {
        $this->stack = array();
    }

    /**
     * Trim the Stack to ensure that it won't exceed $maxsize
     */
    protected function _trimStack()
    {
        if ($this->maxsize && $this->maxsize <= ($stacksize = $this->count())) {
             for ($i=$stacksize; $i>$this->maxsize; $i--) {
                 array_shift($this->stack);
             }
        }
    }
}
?>
