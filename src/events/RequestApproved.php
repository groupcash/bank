<?php
namespace groupcash\bank\events;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class RequestApproved {

    /** @var AccountIdentifier */
    private $issuer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $account;

    /**
     * @param AccountIdentifier $issuer
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $account
     */
    public function __construct(AccountIdentifier $issuer, CurrencyIdentifier $currency, AccountIdentifier $account) {
        $this->currency = $currency;
        $this->account = $account;
        $this->issuer = $issuer;
    }

    /**
     * @return AccountIdentifier
     */
    public function getIssuer() {
        return $this->issuer;
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