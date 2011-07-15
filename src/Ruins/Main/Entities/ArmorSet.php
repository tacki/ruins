<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;

/**
 * @Entity
 * @Table(name="items__armorset")
 */
class ArmorSet extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Ruins\Main\Entities\Character
     */
    protected $character;

    /**
     * @ManyToOne(targetEntity="Ruins\Main\Entities\Items\Armor")
     * @var Ruins\Main\Entities\Items\Armor
     */
    protected $head;

    /**
     * @ManyToOne(targetEntity="Ruins\Main\Entities\Items\Armor")
     * @var Ruins\Main\Entities\Items\Armor
     */
    protected $chest;

    /**
     * @ManyToOne(targetEntity="Ruins\Main\Entities\Items\Armor")
     * @var Ruins\Main\Entities\Items\Armor
     */
    protected $arms;

    /**
     * @ManyToOne(targetEntity="Ruins\Main\Entities\Items\Armor")
     * @var Ruins\Main\Entities\Items\Armor
     */
    protected $legs;

    /**
     * @ManyToOne(targetEntity="Ruins\Main\Entities\Items\Armor")
     * @var Ruins\Main\Entities\Items\Armor
     */
    protected $feet;

    /**
     * Generate Total Armor Class
     * @return int
     */
    public function getTotalArmorClass()
    {
        $total = 	$this->head->getArmorClass() +
                    $this->chest->getArmorClass() +
                    $this->arms->getArmorClass() +
                    $this->legs->getArmorClass() +
                    $this->feet->getArmorClass();

        return $total;
    }
}
?>