<?php
/**
 * Bank Repository
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Repositories;
use Main\Entities\Bank,
    Main\Entities\Character,
    Main\Layers\Money;
use Common\Controller\Registry;

/**
 * Class Name
 * @package Ruins
 */
class BankRepository extends Repository
{

    /**
     * Create an Account at the given Bank
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @return Main\Repositories\BankRepository
     */
    public function createAccount(Character $character, $bankname)
    {
        if (!$this->accountExists($character, $bankname)) {
            $account = new Bank;
            $account->name = $bankname;
            $account->depositor = $character;
            $this->getEntityManager()->persist($account);
        }

        return $this;
    }

    /**
     * Get Bank Account
     * @param Character $character Character Object
     * @param string $bankname Name of the Bank
     * @return object Account Object
     */
    public function getAccount(Character $character, $bankname)
    {
        return $this->findOneBy(array("name" => $bankname, "depositor" => $character));;
    }

    /**
     * Check if a given Character has a Banking account
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @return bool true is an account exists, else false
     */
    public function accountExists(Character $character, $bankname)
    {
        return $this->getAccount($character, $bankname);
    }

    /**
     * Charge the Interest for credits and debits
     * @param Character $character The Character-Object
     * @param string $bankname The Name of the Bank
     * @return Money The Interest given
     */
    public function chargeInterest(Character $character, $bankname)
    {
        $systemConfig = Registry::getMainConfig();

        $balance = $this->getAccount($character, $bankname)->balance;

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

        $this->deposit($character, $bankname, $interest);

        return $interest;
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
        if ($this->getAccount($character, $bankname)) {
            $this->getAccount($character, $bankname)->balance->receive($amount);
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
        if ($this->getAccount($character, $bankname)) {
            $this->getAccount($character, $bankname)->balance->pay($amount);
        }
    }
}