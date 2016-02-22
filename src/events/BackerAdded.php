<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class BackerAdded extends DomainEvent {

    /** @var BackerIdentifier */
    private $backer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $issuer;

    /**
     * @param AccountIdentifier $issuer
     * @param CurrencyIdentifier $currency
     * @param BackerIdentifier $backer
     */
    public function __construct(AccountIdentifier $issuer, CurrencyIdentifier $currency, BackerIdentifier $backer) {
        parent::__construct();

        $this->backer = $backer;
        $this->currency = $currency;
        $this->issuer = $issuer;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
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