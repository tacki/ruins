<?php
/**
 * Money Class
 *
 * Money-Class
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Layers;

/**
 * Includes
 */
require_once("layerbase.php");


/**
 * Money Layer Class
 *
 * Money-Class
 * @package Ruins
 */
class Money extends LayerBase
{
    /**
     * internal money-value
     * @var int
     */
    private $money = 0;

    /**
     * Class Defines
     */
    const MONEY_ALLOW_NEGATIVE_AMOUNT = true;
    const MONEY_GOLD_VALUE 			  =	10000;
    const MONEY_SILVER_VALUE		  =	100;
    const MONEY_COPPER_VALUE		  =	1;


    public function __construct($initialvalue=0)
    {
        $this->money = $initialvalue;
        $this->initLayer($this->money);
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
     * Set the plain Money-Value (in the smallest Currency)
     * @param int $balance Amount
     */
    public function setPlain($money)
    {
        $this->money = (int)$money;
    }

    /**
     * Get the plain Money-Value (in the smallest Currency)
     * @return int Amount
     */
    public function getPlain()
    {
        return $this->money;
    }

    /**
     * Pay something with the Money we manage
     * @param int $amount Amount of Money to pay
     * @param string $currency Currency to use
     * @return bool true if successful, else false
     */
    public function pay($amount, $currency="copper")
    {
        if ($amount instanceof \Layers\Money) {
            $amount = $amount->getPlain();
        }

        $result = $this->_calculate("pay", $amount, $currency);

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
        if ($amount instanceof \Layers\Money) {
            $amount = $amount->getPlain();
        }

        $result = $this->_calculate("receive", $amount, $currency);

        return $result;
    }

    /**
     * Get the Amount of Money splitted by each Currency
     * @param string $currency Currency to get (gold|silver|copper)
     * @return bool Amount of the given Currency we have
     */
    public function getCurrency($currency="")
    {
        switch ($currency) {
            case "gold":
                return floor($this->money/self::MONEY_GOLD_VALUE);

            case "silver":
                return floor(($this->money%self::MONEY_GOLD_VALUE)/self::MONEY_SILVER_VALUE);

            case "copper":
                return floor($this->money%self::MONEY_SILVER_VALUE);

            default:
                return $this->money;
        }
    }

    /**
     * Get the Amount of Money as a full string with pictures
     * @param string $currency The Currency to show
     * @return string HTML-String of the values+their pictures
     */
    public function getCurrencyWithPic($currency="")
    {
        switch ($currency) {
            case "gold":
                return $this->getCurrency($currency) . "<img src=\"templates/common/images/gold.gif\" height=\"10\" alt=\"g\">";

            case "silver":
                return $this->getCurrency($currency) . "<img src=\"templates/common/images/silver.gif\" height=\"10\" alt=\"s\">";

            case "copper":
                return $this->getCurrency($currency) . "<img src=\"templates/common/images/copper.gif\" height=\"10\" alt=\"c\">";

            default:
                return $this->money;
        }
    }

    /**
     * Show complete Amount of Money in this Object with pictures
     * @param bool $showempty Show Currency that has a value of 0
     * @return string HTML-String of the values+their pictures
     */
    public function getAllCurrenciesWithPic($showempty=false)
    {
        $output = "";

        if ($this->getCurrency("gold") || $showempty) {
            $output .= $this->getCurrencyWithPic("gold") . " ";
        }

        if ($this->getCurrency("silver") || $showempty) {
            $output .= $this->getCurrencyWithPic("silver") . " ";
        }

        if ($this->getCurrency("copper") || $showempty) {
            $output .= $this->getCurrencyWithPic("copper");
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
    private function _calculate($action, $amount, $currency="")
    {
        switch ($action) {
            case "pay":
                switch ($currency) {
                    case "gold":
                        if ($this->money >= $amount*self::MONEY_GOLD_VALUE) {
                            $this->money -= $amount*self::MONEY_GOLD_VALUE;
                            return true;
                        } else {
                            return false;
                        }
                        break;

                    case "silver":
                        if ($this->money >= $amount*self::MONEY_SILVER_VALUE) {
                            $this->money -= $amount*self::MONEY_SILVER_VALUE;
                            return true;
                        } else {
                            return false;
                        }
                        break;

                    case "copper":
                        if ($this->money >= $amount*self::MONEY_COPPER_VALUE) {
                            $this->money -= $amount*self::MONEY_COPPER_VALUE;
                            return true;
                        } else {
                            return false;
                        }
                        break;

                    default:
                        if ($this->money >= $amount) {
                            $this->money -= $amount;
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
                        $this->money += $amount*self::MONEY_GOLD_VALUE;
                        return true;
                        break;

                    case "silver":
                        $this->money += $amount*self::MONEY_SILVER_VALUE;
                        return true;
                        break;

                    case "copper":
                        $this->money += $amount*self::MONEY_COPPER_VALUE;
                        return true;
                        break;

                    default:
                        $this->money += $amount;
                        return true;
                        break;
                }
                break;
        }
    }
}
?>
