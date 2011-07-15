<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Main\Entities\EntityBase;
use Ruins\Main\Controller\SkillBase;

/**
 * @Entity
 * @Table(name="battles__members")
 */
class BattleMember extends EntityBase
{
    /**
    * Class constants
    */
    const SIDE_ATTACKERS        = "attackers";
    const SIDE_DEFENDERS        = "defenders";
    const SIDE_NEUTRALS         = "neutrals";
    const STATUS_ACTIVE         = 0;
    const STATUS_INACTIVE       = 1;
    const STATUS_EXCLUDED       = 2;
    const STATUS_BEATEN         = 4;

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Battle", inversedBy="members")
     * @var Ruins\Main\Entities\Battle
     */
    protected $battle;

    /**
     * @ManyToOne(targetEntity="Character")
     * @var Ruins\Main\Entities\Character
     */
    protected $character;

    /**
     * @OneToOne(targetEntity="BattleAction", mappedBy="initiator", cascade={"all"})
     * @var Ruins\Main\Entities\BattleAction
     */
    protected $action;

    /**
     * @Column(length=16)
     * @var string
     */
    protected $side;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $speed;

    /**
     * @Column(type="boolean")
     */
    protected $token;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $status;

    public function __construct()
    {
        // Default Values
        $this->token      = false;
        $this->status     = 0;
    }

    /**
    * Get the 'opposite' Side, the Character is fighting at
    * @return const self::SIDE_DEFENDERS | self::SIDE_ATTACKERS | self::SIDE_NEUTRALS
    */
    public function getOppositeSide()
    {
        if ($this->side == self::SIDE_ATTACKERS) {
            return self::SIDE_DEFENDERS;
        } elseif ($this->side == self::SIDE_DEFENDERS) {
            return self::SIDE_ATTACKERS;
        } else {
            return self::SIDE_NEUTRALS;
        }
    }

    /**
     * Set Memberstatus to Active
     */
    public function setActive()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * Set Memberstatus to Inactive
     */
    public function setInactive()
    {
        $this->status = self::STATUS_INACTIVE;
    }

    /**
     * Set Memberstatus to Excluded
     */
    public function setExcluded()
    {
        $this->status = self::STATUS_EXCLUDED;
    }

    /**
     * Set Memberstatus to Beaten
     */
    public function setBeaten()
    {
        $this->status = self::STATUS_BEATEN;
    }

    /**
     * Set Action of this Member
     * @param int $target
     * @param Ruins\Main\Controller\SkillBase $skill
     */
    public function setAction($target, Skillbase $skill)
    {
/**
 * 		THIS IS BAD!
 *
        $em = Registry::getEntityManager();

        if (!$this->hasMadeAnAction()) {
            $newAction             = new \Main\Entities\BattleAction;
            $newAction->battle     = $this->battle;
            $newAction->initiator  = $this;
            $newAction->targets->add($target);
            $newAction->skill      = $skill->getEntity();

            $em->persist($newAction);

            // Add to Battle Actions List
            $this->battle->actions->add($newAction);

            // Set own Action
            $this->action = $newAction;
        }
*/
    }

    /**
     * Check if Member made an Action
     * @return bool
     */
    public function hasMadeAnAction()
    {
        if ($this->action) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Action of this Member
     * @return Ruins\Main\Entities\BattleAction
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Check if a Member is Active
     * @return bool
     */
    public function isActive()
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Check if a Member is Inactive
    * @return bool
    */
    public function isInactive()
    {
        if ($this->status === self::STATUS_INACTIVE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if this Character is neutral
     * @return boolean
     */
    public function isNeutral()
    {
        if ($this->side === self::SIDE_NEUTRALS
            || $this->status === self::STATUS_BEATEN
            || $this->status === self::STATUS_EXCLUDED) {
            return true;
        }

        return false;
    }
}
?>