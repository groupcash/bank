<?php
namespace groupcash\bank\events;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Authorization;

class IssuerAuthorized extends DomainEvent {

    /** @var Authorization */
    private $authorization;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $issuer;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $issuer
     * @param Authorization $authorization
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $issuer, Authorization $authorization) {
        parent::__construct();

        $this->authorization = $authorization;
        $this->currency = $currency;
        $this->issuer = $issuer;
    }

    /**
     * @return Authorization
     */
    public function getAuthorization() {
        return $this->authorization;
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
    public function getIssuer() {
        return $this->issuer;
    }
}