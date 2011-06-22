<?php
/**
 * Banking Systemclass
 *
 * Class to manage Banks in all Areas
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Manager;
use Main\Entities,
    Main\Layers\Money;

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
    public function accountExists(Entities\Character $character, $bankname)
    {
        return self::getAccount($character, $bankname);
    }

    /**
     * Get Bank Account
     * @param Character $character Character Object
     * @param string $bankname Name of the Bank
     * @return object Account Object
     */
    public function getAccount(Entities\Character $character, $bankname)
    {
        $qb = getQueryBuilder();

        $result = $qb   ->select("bank")
                        ->from("Main:Bank", "bank")
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
    public function getBalance(Entities\Character $character, $bankname)
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
    public function createAccount(Entities\Character $character, $bankname)
    {
        global $em;

        $newAccount = new Entities\Bank;
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
    public function chargeInterest(Entities\Character $character, $bankname)
    {
        global $systemConfig;

        $balance = self::getBalance($character, $bankname);

        if ($balance->getPlain() >= 0) {
            // we have credit - default to 3%
            $interestrate = $systemConfig->get($bankname."_credit_interest", 3);
        } else {
            // we have debit - default to 7%
            $interestrate = $systemConfig->get($bankname."_debit_interest", 7);
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
    public function deposit(Entities\Character $character, $bankname, $amount)
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
    public function withdraw(Entities\Character $character, $bankname, $amount)
    {
        if ($balance = self::getBalance($character, $bankname)) {
            $balance->pay($amount);
        }
    }
}
?>
