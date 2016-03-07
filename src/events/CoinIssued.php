<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Coin;

class CoinIssued extends DomainEvent {

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $issuer;

    /** @var BackerIdentifier */
    private $backer;

    /** @var Coin */
    private $coin;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $issuer
     * @param BackerIdentifier $backer
     * @param Coin $coin
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $issuer, BackerIdentifier $backer, Coin $coin) {
        parent::__construct();

        $this->currency = $currency;
        $this->issuer = $issuer;
        $this->backer = $backer;
        $this->coin = $coin;
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
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @return Coin
     */
    public function getCoin() {
        return $this->coin;
    }
}