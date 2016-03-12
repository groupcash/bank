<?php
namespace groupcash\bank\events;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\value\Fraction;

class CoinsRequested extends DomainEvent {

    /** @var AccountIdentifier */
    private $account;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var Fraction */
    private $value;

    /**
     * @param AccountIdentifier $account
     * @param CurrencyIdentifier $currency
     * @param Fraction $value
     */
    public function __construct(AccountIdentifier $account, CurrencyIdentifier $currency, Fraction $value) {
        parent::__construct();

        $this->account = $account;
        $this->currency = $currency;
        $this->value = $value;
    }

    /**
     * @return AccountIdentifier
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return Fraction
     */
    public function getValue() {
        return $this->value;
    }
}