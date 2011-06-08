<?php
/**
 * Armorset Class
 *
 * Class to handle the single Armorparts
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: armorset.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Item Class
 *
 * Base Item-Class
 * @package Ruins
 */
class Armorset extends DBObject
{
    public $head;

    public $chest;

    public $arms;

    public $legs;

    public $feet;

    /**
     * constructor - load the default values and initialize the attributes
     */
    function __construct(Character $char=NULL)
    {
        // Call Constructor of the Parent-Class
        parent::__construct();

        // Initialize Attributes
        $this->head		= new Armor;
        $this->chest	= new Armor;
        $this->arms		= new Armor;
        $this->legs		= new Armor;
        $this->feet		= new Armor;

        if ($char && $armorSetID = $this->_getArmorSetIDFromCharacter($char)) {
            $this->load($armorSetID);
        }
    }

    /**
     * @see includes/classes/BaseObject#mod_postload()
     */
    public function mod_postload()
    {
        // Call mod_postload from Parent-Class
        parent::mod_postload();

        $this->head->load($this->headid);
        $this->chest->load($this->chestid);
        $this->arms->load($this->armsid);
        $this->legs->load($this->legsid);
        $this->feet->load($this->feetid);
    }

    public function getTotalArmorClass()
    {
        $total = 	$this->head->getArmorClass() +
                    $this->chest->getArmorClass() +
                    $this->arms->getArmorClass() +
                    $this->legs->getArmorClass() +
                    $this->feet->getArmorClass();

        return $total;
    }

    private function _getArmorSetIDFromCharacter(Character $char)
    {
        if (!$result = SessionStore::readCache("armorSetID_".$char->id)) {
            $dbqt = new QueryTool();

            $qResult = $dbqt->select("id")
                            ->from("armorsets")
                            ->where("characterid=".$dbqt->quote($char->id))
                            ->exec()
                            ->fetchOne();

            if ($qResult) {
                $result = $qResult;
            } else {
                $result = false;
            }

            //XXX: Remove Cache if you want to make it possible to switch armorSets
            SessionStore::writeCache("armorSetID_".$char->id, $result);
        }

        return $result;
    }
}
?>
