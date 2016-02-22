<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class CurrencyAdded extends DomainEvent {

    /** @var BackerIdentifier */
    private $backer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $issuer;

    /**
     * @param BackerIdentifier $backer
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $issuer
     */
    public function __construct(BackerIdentifier $backer, CurrencyIdentifier $currency, AccountIdentifier $issuer) {
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