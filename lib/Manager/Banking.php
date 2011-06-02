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
 * Namespaces
 */
namespace Manager;
use \Entities\Character;
use \Layers\Money;

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
class Banking
{
    /**
     * Check if a given Character has a Banking account
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @return bool true is an account exists, else false
     */
    public function accountExists(Character $character, $bankname)
    {
        return self::getAccount($character, $bankname);
    }

    /**
     * Get Bank Account
     * @param Character $character Character Object
     * @param string $bankname Name of the Bank
     * @return object Account Object
     */
    public function getAccount(Character $character, $bankname)
    {
/*        global $em;

        $result = $em    ->getRepository("Entities\Bank")
                         ->findOneBy(array("name" => $bankname, "depositor" => $character->id));
*/
        $qb = getQueryBuilder();

        $result = $qb   ->select("bank")
                        ->from("Entities\Bank", "bank")
                        ->where("bank.name = ?1")->setParameter(1, $bankname)
                        ->andWhere("bank.depositor = ?2")->setParameter(2, $character)
                        ->getQuery()
                        ->getOneOrNullResult();

        return $result;
    }

    /**
     * Get the Balance of a Characters Banking Account
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @return int the current Balance of the Account
     */
    public function getBalance(Character $character, $bankname)
    {
        if ($account = self::getAccount($character, $bankname)) {
            return $account->balance;
        }
    }

    /**
     * Create an Account at the given Bank
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @return bool true if successful, else false
     */
    public function createAccount(Character $character, $bankname)
    {
        global $em;

        $newAccount = new \Entities\Bank;
        $newAccount->name = $bankname;
        $newAccount->depositor = $character;
        $em->persist($newAccount);

        $em->flush();

        return $newAccount;
    }

    /**
     * Charge the Interest for credits and debits
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @return Money The Interest given
     */
    public function chargeInterest(Character $character, $bankname)
    {
        global $config;

        $balance = self::getBalance($character, $bankname);

        if ($balance->getPlain() >= 0) {
            // we have credit - default to 3%
            $interestrate = $config->get($bankname."_credit_interest", 3);
        } else {
            // we have debit - default to 7%
            $interestrate = $config->get($bankname."_debit_interest", 7);
        }

        // if we have debit, $interest will be a negative amount
        $interest = ceil (($balance->getPlain() / 100) * $interestrate);

        $interest = new Money($interest);

        self::deposit($character, $bankname, $interest);

        return new $interest;
    }

    /**
     * Deposit Money to an Account
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @param int $amount Amount of Money
     * @return bool true if successful, else false
     */
    public function deposit(Character $character, $bankname, $amount)
    {
        if ($balance = self::getBalance($character, $bankname)) {
            $balance->receive($amount);
        }
    }

    /**
     * Withdraw Money from an Account
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @param int $amount Amount of Money
     * @return bool true if successful, else false
     */
    public function withdraw(Character $character, $bankname, $amount)
    {
        if ($balance = self::getBalance($character, $bankname)) {
            $balance->pay($amount);
        }
    }
}
?>
