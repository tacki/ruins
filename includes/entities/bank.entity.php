<?php
namespace Entities;

require_once 'entitybase.php';

/**
 * @Entity
 * @Table(name="banks")
 */
class Bank extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=255) */
    protected $name;

    /**
     * @ManyToOne(targetEntity="Character")
     */
    protected $depositor;

    /** @Column(type="integer") */
    protected $balance;

    const MONEY_GOLD_VALUE     = 10000;
    const MONEY_SILVER_VALUE   = 100;
    const MONEY_COPPER_VALUE   = 1;

    /**
     * Pay something with the Money we manage
     * @param int $amount Amount of Money to pay
     * @param string $currency Currency to use
     * @return bool true if successful, else false
     */
    public function pay($amount, $currency="copper")
    {
        if ($amount instanceof Money) {
            $result = 	$this->_calculate("pay", $amount->getDetailed("gold"), "gold") &&
                        $this->_calculate("pay", $amount->getDetailed("silver"), "silver") &&
                        $this->_calculate("pay", $amount->getDetailed("copper"), "copper");

            if ($result && $this->parent instanceof Character) {
                $this->parent->debuglog->add("Pay "
                                    . $amount->getDetailed("gold") . " Gold, "
                                    . $amount->getDetailed("silver") . " Silver and "
                                    . $amount->getDetailed("copper") . " Copper"
                                    , "veryverbose");
            }

            return $result;
        } else {
            if (MONEY_ALLOW_NEGATIVE_AMOUNT && $amount < 0) {
                $this->receive($amount, $currency);
            } elseif ($amount < 0) {
                return false;
            }
        }

        $result = $this->_calculate("pay", $amount, $currency);

        if ($result && $this->parent instanceof Character) {
            $this->parent->debuglog->add("Pay {$amount} Copper", "veryverbose");
        }

        return $result;
    }

    /**
     * Receive Money and put it to the Money and we manage
     * @param int $amount Amount of Money to receive
     * @param string $currency Currency to use
     * @return bool true if successful, else false
     */
    public function receive($amount, $currency="copper")
    {
        global $user;

        if ($amount instanceof Money) {
            $result = 	$this->_calculate("receive", $amount->getDetailed("gold"), "gold") &&
                        $this->_calculate("receive", $amount->getDetailed("silver"), "silver") &&
                        $this->_calculate("receive", $amount->getDetailed("copper"), "copper");

            if ($result) {
                $user->addDebugLog("Receive "
                                    . $amount->getDetailed("gold") . " Gold, "
                                    . $amount->getDetailed("silver") . " Silver and "
                                    . $amount->getDetailed("copper") . " Copper"
                                    , "veryverbose");
            }
            return $result;
        } else {
            if (MONEY_ALLOW_NEGATIVE_AMOUNT && $amount < 0) {
                $this->pay($amount, $currency);
            } elseif ($amount < 0) {
                return false;
            }
        }

        $result = $this->_calculate("receive", $amount, $currency);

        if ($result && $this->parent instanceof Character) {
            $this->parent->debuglog->add("Receive {$amount} Copper", "veryverbose");
        }

        return $result;
    }

    /**
     * Get the Amount of Money splitted by each Currency
     * @param string $currency Currency to get (gold|silver|copper)
     * @return bool Amount of the given Currency we have
     */
    public function getDetailed($currency="")
    {
        switch ($currency) {
            case "gold":
                return floor($this->balance/self::MONEY_GOLD_VALUE); // Goldvalue = 10000 Copper

            case "silver":
                return floor(($this->balance%self::MONEY_GOLD_VALUE)/self::MONEY_SILVER_VALUE); // Silver Value = 100 Copper

            case "copper":
                return floor($this->balance%self::MONEY_SILVER_VALUE);

            default:
                return $this->balance;
        }
    }

    /**
     * Get the Amount of Money as a full string with pictures
     * @param string $currency The Currency to show
     * @return string HTML-String of the values+their pictures
     */
    public function detailedWithPic($currency="")
    {
        switch ($currency) {
            case "gold":
                return $this->getDetailed($currency) . "<img src=\"templates/common/images/gold.gif\" height=\"10\" alt=\"g\">";

            case "silver":
                return $this->getDetailed($currency) . "<img src=\"templates/common/images/silver.gif\" height=\"10\" alt=\"s\">";

            case "copper":
                return $this->getDetailed($currency) . "<img src=\"templates/common/images/copper.gif\" height=\"10\" alt=\"c\">";

            default:
                return $this->balance;
        }
    }

    /**
     * Show complete Amount of Money in this Object with pictures
     * @param bool $showempty Show Currency that has a value of 0
     * @return string HTML-String of the values+their pictures
     */
    public function fullDetailedWithPic($showempty=false)
    {
        $output = "";

        if ($this->getDetailed("gold") || $showempty) {
            $output .= $this->detailedWithPic("gold") . " ";
        }

        if ($this->getDetailed("silver") || $showempty) {
            $output .= $this->detailedWithPic("silver") . " ";
        }

        if ($this->getDetailed("copper") || $showempty) {
            $output .= $this->detailedWithPic("copper");
        }

        return trim($output);
    }

    /*
     * Calculate Changes to the Money we manage
     * @param string action What to do? (Pay|Receive)
     * @param int $amount Amount of Money to handle
     * @param string $currency Currency to use
     * @return bool true if successful, else false
     */
    private function _calculate($action, $amount, $currency)
    {
        switch ($action) {
            case "pay":
                switch ($currency) {
                    case "gold":
                        if ($this->balance >= $amount*self::MONEY_GOLD_VALUE) {
                            $this->balance -= $amount*self::MONEY_GOLD_VALUE;
                            return true;
                        } else {
                            return false;
                        }
                        break;

                    case "silver":
                        if ($this->balance >= $amount*self::MONEY_SILVER_VALUE) {
                            $this->balance -= $amount*self::MONEY_SILVER_VALUE;
                            return true;
                        } else {
                            return false;
                        }
                        break;

                    case "copper":
                        if ($this->balance >= $amount*self::MONEY_COPPER_VALUE) {
                            $this->balance -= $amount*self::MONEY_COPPER_VALUE;
                            return true;
                        } else {
                            return false;
                        }
                        break;
                }
                break;

            case "receive":
                switch ($currency) {
                    case "gold":
                        $this->balance += $amount*self::MONEY_GOLD_VALUE;
                        return true;
                        break;

                    case "silver":
                        $this->balance += $amount*self::MONEY_SILVER_VALUE;
                        return true;
                        break;

                    case "copper":
                        $this->balance += $amount*self::MONEY_COPPER_VALUE;
                        return true;
                        break;
                }
                break;
        }
    }
}