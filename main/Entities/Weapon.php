<?php
/**
 * Namespaces
 */
namespace Entities;

/**
 * @Entity
 * @Table(name="weapons")
 */
class Weapon extends Item
{
    /** @Column(length=64) */
    protected $class;

    /**
     * @Column(type="integer")
     */
    protected $damage_min;

    /**
     * @Column(type="integer")
     */
    protected $damage_max;

    public function __construct()
    {
        // Default Values
        $this->damage_min = 0;
        $this->damage_max = 0;
    }

    /**
     * Calculate the Minimum Damage of this Weapon
     * @return int Minimum Damage (buff included)
     */
    public function getMinDamage()
    {
        if ($this->buff) {
 /*           $result = 	$this->weapondamage_min +
                        $this->buff->weapondamage_min_mod +
                        $this->buff->weapondamage_mod; */
        } else {
            $result = $this->damage_min;
        }

        return $result;
    }

    /**
     * Calculate the Maximum Damage of this Weapon
     * @return int Maximum Damage (buff included)
     */
    public function getMaxDamage()
    {
        if ($this->buff) {
 /*           $result = 	$this->weapondamage_max +
                        $this->buff->weapondamage_max_mod +
                        $this->buff->weapondamage_mod;*/
        } else {
            $result = $this->damage_max;
        }

        return $result;
    }

    /**
     * Generate a 'Min - Max' Damage Output
     * @param bool $output Set to true if you want to output it directly
     * @return string HTML-formed Output of Min-Max Damage
     */
    public function showDamage($output=false)
    {
        $showclass = "itemstat";
        $improved  = false;
        $decreased = false;
        $mindamage = $this->getMinDamage();
        $maxdamage = $this->getMaxDamage();

        // Improvements are dominant
        if ($mindamage > $this->damage_min || $maxdamage > $this->damage_max) {
            $improved = true;
        } elseif ($mindamage < $this->damage_min || $maxdamage < $this->damage_max) {
            $decreased = true;
        }

        if ($improved) {
            $showclass = "itemstat_improved";
        } elseif ($decreased) {
            $showclass = "itemstat_decreased";
        }

        $outputText = "<span class='" . $showclass . "'>";
        $outputText .= $mindamage . " - " . $maxdamage;
        $outputText .= "</span>";

        if ($output) {
            $outputobject = getOutputObject();
            $outputobject->output($outputText, true);
        } else {
            return $outputText;
        }
    }
}