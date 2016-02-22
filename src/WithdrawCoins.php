<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Fraction;

class WithdrawCoins implements Command {

    /** @var Authentication */
    private $account;

    /** @var Fraction */
    private $amount;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param Authentication $account
     * @param CurrencyIdentifier $currency
     * @param Fraction $amount
     */
    public function __construct(Authentication $account, CurrencyIdentifier $currency, Fraction $amount) {
        $this->account = $account;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @return Authentication
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * @return Fraction
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }
}