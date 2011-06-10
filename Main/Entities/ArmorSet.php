<?php
/**
 * Namespaces
 */
namespace Main\Entities;

/**
 * @Entity
 * @Table(name="items__armorset")
 */
class ArmorSet extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $character;

    /**
     * @ManyToOne(targetEntity="Main\Entities\Items\Armor")
     */
    protected $head;

    /**
     * @ManyToOne(targetEntity="Main\Entities\Items\Armor")
     */
    protected $chest;

    /**
     * @ManyToOne(targetEntity="Main\Entities\Items\Armor")
     */
    protected $arms;

    /**
     * @ManyToOne(targetEntity="Main\Entities\Items\Armor")
     */
    protected $legs;

    /**
     * @ManyToOne(targetEntity="Main\Entities\Items\Armor")
     */
    protected $feet;

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