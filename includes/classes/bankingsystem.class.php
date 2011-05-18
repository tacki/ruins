<?php
/**
 * Banking Systemclass
 *
 * Class to manage Banks in all Areas
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: bankingsystem.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Banking Systemclass
 *
 * Class to manage Banks in all Areas
 * @package Ruins
 */
class BankingSystem
{
    /**
     * Check if a given Character has a Banking account
     * @param Character $char The Character-Object
     * @param string $bankname The Name of the Bank
     * @return bool true is an account exists, else false
     */
    public function accountExists(Character $char, $bankname)
    {
        if (!$result = SessionStore::readCache("bankingAccountExists_".$char->id."_".$bankname)) {
            $dbqt = new QueryTool();
            $numrows = $dbqt->select("id")
                            ->from("banking")
                            ->where("bankname=".$dbqt->quote($bankname))
                            ->where("characterid=".$dbqt->quote($char->id))
                            ->exec()
                            ->numRows();

            if ($numrows) {
                $result = true;
            } else {
                $result = false;
            }

            SessionStore::writeCache("bankingAccountExists_".$char->id."_".$bankname, $result);
        }

        return $result;
    }

    /**
     * Get the Balance of a Characters Banking Account
     * @param Character $char The Character-Object
     * @param string $bankname The Name of the Bank
     * @return int the current Balance of the Account
     */
    public function getBalance(Character $char, $bankname)
    {
        $dbqt = new QueryTool();

        $result = $dbqt	->select("balance")
                        ->from("banking")
                        ->where("bankname=".$dbqt->quote($bankname))
                        ->where("characterid=".$dbqt->quote($char->id))
                        ->exec()
                        ->fetchOne();

        return $result;
    }

    /**
     * Create an Account at the given Bank
     * @param Character $char The Character-Object
     * @param string $bankname The Name of the Bank
     * @return bool true if successful, else false
     */
    public function createAccount(Character $char, $bankname)
    {
        $accountdata = array(	"bankname" => $bankname,
                                "characterid" => $char->id,
                                "balance" => 0);

        $dbqt = new QueryTool();

        $result = $dbqt	->insertinto("banking")
                        ->set($accountdata)
                        ->exec();

        return $result;
    }

    /**
     * Charge the Interest for credits and debits
     * @param Character $char The Character-Object
     * @param string $bankname The Name of the Bank
     * @return Money The Interest given
     */
    public function chargeInterest(Character $char, $bankname)
    {
        global $config;

        $balance = self::getBalance($char, $bankname);

        if ($balance >= 0) {
            // we have credit - default to 3%
            $interestrate = $config->get($bankname."_credit_interest", 3);
        } else {
            // we have debit - default to 7%
            $interestrate = $config->get($bankname."_debit_interest", 7);
        }

        // if we have debit, $interest will be a negative amount
        $interest = ceil (($balance / 100) * $interestrate);
        ModuleSystem::enableManagerModule($interest, "Money");

        self::deposit($char, $bankname, $interest);

        return $interest;
    }

    /**
     * Deposit Money to an Account
     * @param Character $char The Character-Object
     * @param string $bankname The Name of the Bank
     * @param int $amount Amount of Money
     * @return bool true if successful, else false
     */
    public function deposit(Character $char, $bankname, $amount)
    {
        if ($amount instanceof Money) {
            $amount = $amount->detailed();
        }

        $dbqt = new QueryTool();

        $result = $dbqt	->update("banking")
                        ->set(array("balance" => self::getBalance($char, $bankname)+(int)$amount))
                        ->where("bankname=".$dbqt->quote($bankname))
                        ->where("characterid=".$dbqt->quote($char->id))
                        ->exec();

        return $result;
    }

    /**
     * Withdraw Money from an Account
     * @param Character $char The Character-Object
     * @param string $bankname The Name of the Bank
     * @param int $amount Amount of Money
     * @return bool true if successful, else false
     */
    public function withdraw(Character $char, $bankname, $amount)
    {
        if ($amount instanceof Money) {
            $amount = $amount->detailed();
        }

        $dbqt = new QueryTool();

        $result = $dbqt	->update("banking")
                        ->set(array("balance" => self::getBalance($char, $bankname)-(int)$amount))
                        ->where("bankname=".$dbqt->quote($bankname))
                        ->where("characterid=".$dbqt->quote($char->id))
                        ->exec();

        return $result;
    }
}
?>
