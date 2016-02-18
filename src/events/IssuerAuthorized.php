<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Authorization;

class IssuerAuthorized extends DomainEvent {

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var Authorization */
    private $authorization;

    /**
     * @param CurrencyIdentifier $currency
     * @param Authorization $authorization
     */
    public function __construct(CurrencyIdentifier $currency, Authorization $authorization) {
        parent::__construct();
        $this->currency = $currency;
        $this->authorization = $authorization;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return Authorization
     */
    public function getAuthorization() {
        return $this->authorization;
    }
}