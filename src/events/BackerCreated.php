<?php
namespace groupcash\bank\events;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\signing\Binary;

class BackerCreated extends DomainEvent {

    /** @var Binary */
    private $key;

    /** @var BackerIdentifier */
    private $backer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $issuer;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $issuer
     * @param BackerIdentifier $backer
     * @param Binary $key
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $issuer, BackerIdentifier $backer, Binary $key) {
        parent::__construct();

        $this->backer = $backer;
        $this->currency = $currency;
        $this->issuer = $issuer;
        $this->key = $key;
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

    /**
     * @return Binary
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }
}