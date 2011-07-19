<?php
/**
 * Html Element Manager Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Manager;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Common\Controller\Form;
use Ruins\Common\Controller\Table;
use Ruins\Common\Controller\SimpleTable;
use Ruins\Main\Controller\ClassicChat;
use Ruins\Common\Controller\Request;
use Ruins\Common\Exceptions\Error;

/**
 * Request Handler Class
 * @package Ruins
 */
class HtmlElementManager
{
    /**
    * Page Elements (chat, table, forms, ...)
    * @var array
    */
    protected static $elements = array();

    /**
    * Add a new Element to _element-array
    * @param string $type Element-Type
    * @param string $name Element-Name
    * @param object $object The Element
    * @param bool $overwrite Force overwrite of existing
    * @throws Error
    * @return object The added object
    */
    public static function addElement($type, $name, $object, $overwrite=false)
    {
        if (!is_array(self::$elements[$type])) self::$elements[$type] = array();

        if (!isset(self::$elements[$type][$name]) || $overwrite) {
            self::$elements[$type][$name] = $object;
        } else {
            throw new Error("Element ".$type."->".$name." already exists.");
        }

        return $object;
    }

    /**
     * Return the given Element
     * @param string $name
     * @throws Error
     * @return object
     */
    public static function getElement($type, $name)
    {
        if (isset(self::$elements[$type][$name])) {
            return self::$elements[$type][$name];
        } else {
            throw new Error ("Element ".$type."->".$name." does not exist");
        }
    }

    /**
     * Close Form Object
     * @param string $name
     */
    public static function deleteElement($type, $name)
    {
        if (isset(self::$elements[$type][$name])) {
            unset (self::$elements[$type][$name]);
        } else {
            throw new Error ("Element ".$type."->".$name." does not exist");
        }
    }

    /**
    * Add a new HTMLForm to the Page
    * @param string $name Name of the HTMLForm
    * @param bool $directoutput Output directly with $page->output()
    * @param bool $overwrite Overwrite existing
    * @return Ruins\Common\Controller\Form The Form Object
    */
    public function addForm($name, OutputObjectInterface $outputObject=null, $overwrite=false)
    {
        if ($outputObject) {
            $result = self::addElement("Form", $name, new Form($outputObject), $overwrite);
        } else {
            $result = self::addElement("Form", $name, new Form(), $overwrite);
        }

        return $result;
    }

    /**
     * Return given Form Object
     * @param string $name
     * @return Ruins\Common\Controller\Form The Form Object
     */
    public function getForm($name)
    {
        return self::getElement("Form", $name);
    }

    /**
     * Close Form Object
     * @param string $name
     */
    public function closeForm($name)
    {
        return self::deleteElement("Form", $name);
    }

    /**
     * Add a new HTMLTable to the Page
     * @param string $name Name of the HTMLTable
     * @param bool $directoutput Output directly with $page->output()
     * @param bool $overwrite Overwrite existing
     * @return Ruins\Common\Controller\Table The Table Object
     */
    public function addTable($name, OutputObjectInterface $outputObject=null, $overwrite=false)
    {
        if ($outputObject) {
            $result = self::addElement("Table", $name, new Table($outputObject), $overwrite);
        } else {
            $result = self::addElement("Table", $name, new Table(), $overwrite);
        }

        return $result;
    }

    /**
     * Return given Table Object
     * @param string $name
     * @return Ruins\Common\Controller\Table The Table Object
     */
    public function getTable($name)
    {
        return self::getElement("Table", $name);
    }

    /**
     * Close Table Object
     * @param string $name
     */
    public function closeTable($name)
    {
        return self::deleteElement("Table", $name);
    }

    /**
     * Add a new Chat to the Page
     * @param string $name Name of the Chat
     * @return Ruins\Common\Controller\Chat The Chat Object
     */
    public function addChat($name, OutputObjectInterface $outputObject=null)
    {
        // always overwrite Chat
        $result = self::addElement("Chat", $name, new ClassicChat($outputObject, $name), true);

        return $result;
    }

    /**
     * Return given Chat Object
     * @param string $name
     * @return Ruins\Common\Controller\Chat The Chat Object
     */
    public function getChat($name)
    {
        return self::getElement("Chat", $name);
    }

    /**
     * Close Chat Object
     * @param string $name
     */
    public function closeChat($name)
    {
        return self::deleteElement("Chat", $name);
    }

    /**
     * Add a new simple HTMLTable to the Page
     * @param string $name Name of the simple HTMLTable
     * @param bool $directoutput Output directly with $page->output()
     * @param bool $overwrite Overwrite existing
     * @return Ruins\Common\Controller\SimpleTable The Table Object
     */
    public function addSimpleTable($name, OutputObjectInterface $outputObject=null, $overwrite=false)
    {
        if ($outputObject) {
            $result = self::addElement("SimpleTable", $name, new SimpleTable($outputObject), $overwrite);
        } else {
            $result = self::addElement("SimpleTable", $name, new SimpleTable(), $overwrite);
        }

        return $result;
    }

    /**
     * Return given SimpleTable Object
     * @param string $name
     * @return Ruins\Common\Controller\SimpleTable The Chat Object
     */
    public function getSimpleTable($name)
    {
        return self::getElement("SimpleTable", $name);
    }

    /**
     * Close SimpleTable Object
     * @param string $name
     */
    public function closeSimpleTable($name)
    {
        return self::deleteElement("SimpleTable", $name);
    }
}