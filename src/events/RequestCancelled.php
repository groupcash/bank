<?php
namespace groupcash\bank\events;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class RequestCancelled {

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $account;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $account
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $account) {
        $this->currency = $currency;
        $this->account = $account;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return AccountIdentifier
     */
    public function getAccount() {
        return $this->account;
    }
}