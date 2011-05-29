<?php
/**
 * Money Class
 *
 * Money-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Class Defines
 */
define("MONEY_ALLOW_NEGATIVE_AMOUNT", 	true);
define("MONEY_GOLD_VALUE", 				10000);
define("MONEY_SILVER_VALUE",			100);
define("MONEY_COPPER_VALUE",			1);

/**
 * Money Class
 *
 * Money-Class
 * @package Ruins
 */
class MoneyLayer
{
    /**
     * internal money-value
     * @var int
     */
    private $money=0;


    public function __construct($initialvalue=0)
    {
        $this->money = $initialvalue;
    }

    /**
     * Return $this->money if Object used as String
     * @return int The current Money we manage
     */
    public function __toString()
    {
        return (string)$this->money;
    }

    /**
     * Pay something with the Money we manage
     * @param int $amount Amount of Money to pay
     * @param string $currency Currency to use
     * @return bool true if successful, else false
     */
    public function pay($amount, $currency="copper")
    {
        if ($amount instanceof Money) {
            $result = 	$this->_calculate("pay", $amount->detailed("gold"), "gold") &&
                        $this->_calculate("pay", $amount->detailed("silver"), "silver") &&
                        $this->_calculate("pay", $amount->detailed("copper"), "copper");

            if ($result && $this->parent instanceof Character) {
                $this->parent->debuglog->add("Pay "
                                    . $amount->detailed("gold") . " Gold, "
                                    . $amount->detailed("silver") . " Silver and "
                                    . $amount->detailed("copper") . " Copper"
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
            $result = 	$this->_calculate("receive", $amount->detailed("gold"), "gold") &&
                        $this->_calculate("receive", $amount->detailed("silver"), "silver") &&
                        $this->_calculate("receive", $amount->detailed("copper"), "copper");

            if ($result) {
                $user->addDebugLog("Receive "
                                    . $amount->detailed("gold") . " Gold, "
                                    . $amount->detailed("silver") . " Silver and "
                                    . $amount->detailed("copper") . " Copper"
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
     * @param int $currency Currency to get
     * @return bool Amount of the given Currency we have
     */
    public function detailed($currency="")
    {
        switch ($currency) {
            case "gold":
                return floor($this->money/MONEY_GOLD_VALUE);

            case "silver":
                return floor(($this->money%MONEY_GOLD_VALUE)/MONEY_SILVER_VALUE);

            case "copper":
                return floor($this->money%MONEY_SILVER_VALUE);

            default:
                return $this->money;
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
                return $this->detailed($currency) . "<img src=\"templates/common/images/gold.gif\" height=\"10\" alt=\"g\">";

            case "silver":
                return $this->detailed($currency) . "<img src=\"templates/common/images/silver.gif\" height=\"10\" alt=\"s\">";

            case "copper":
                return $this->detailed($currency) . "<img src=\"templates/common/images/copper.gif\" height=\"10\" alt=\"c\">";

            default:
                return $this->money;
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

        if ($this->detailed("gold") || $showempty) {
            $output .= $this->detailedWithPic("gold") . " ";
        }

        if ($this->detailed("silver") || $showempty) {
            $output .= $this->detailedWithPic("silver") . " ";
        }

        if ($this->detailed("copper") || $showempty) {
            $output .= $this->detailedWithPic("copper");
        }

        return trim($output);
    }

    /**
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
                        if ($this->money >= $amount*MONEY_GOLD_VALUE || MONEY_ALLOW_NEGATIVE_AMOUNT) {
                            $this->money -= $amount*MONEY_GOLD_VALUE;
                            return true;
                        } else {
                            return false;
                        }
                        break;

                    case "silver":
                        if ($this->money >= $amount*MONEY_SILVER_VALUE || MONEY_ALLOW_NEGATIVE_AMOUNT) {
                            $this->money -= $amount*MONEY_SILVER_VALUE;
                            return true;
                        } else {
                            return false;
                        }
                        break;

                    case "copper":
                        if ($this->money >= $amount*MONEY_COPPER_VALUE || MONEY_ALLOW_NEGATIVE_AMOUNT) {
                            $this->money -= $amount*MONEY_COPPER_VALUE;
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
                        $this->money += $amount*MONEY_GOLD_VALUE;
                        return true;
                        break;

                    case "silver":
                        $this->money += $amount*MONEY_SILVER_VALUE;
                        return true;
                        break;

                    case "copper":
                        $this->money += $amount*MONEY_COPPER_VALUE;
                        return true;
                        break;
                }
                break;
        }
    }
}
?>
