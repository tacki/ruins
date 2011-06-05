<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @Table(name="items__armors")
 */
class Armor extends Item
{
    /**
     * @Column(type="integer")
     */
    protected $armorclass;

    public function __construct()
    {
        // Default Values
        $this->armorclass = 1;
    }

    /**
     * Calculate the Armorclass of this Armorpart
     * @return int Armorclass
     */
    public function getArmorClass()
    {
        if ($this->buff) {
            $result = 	$this->armorclass +
                        $this->buff->armorclass_mod;
        } else {
            $result = $this->armorclass;
        }

        return $result;
    }

    /**
     * Generate a Armorclass Output
     * @param bool $output Set to true if you want to output it directly
     * @return string HTML-formed Output of Armorclass
     */
    public function showArmorClass($output=false)
    {
        $modified = false;

        if ($this->getArmorClass() != $this->armorclass) {
            $modified = true;
        }

        $outputText = "<span class='" . ($modified?"weaponstat_modified":"weaponstat") . "'>";
        $outputText .= $this->getArmorClass();
        $outputText .= "</span>";

        if ($output) {
            $outputobject = getOutputObject();
            $outputobject->output($outputText, true);
        } else {
            return $outputText;
        }
    }
}