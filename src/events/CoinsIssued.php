<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Coin;

class CoinsIssued extends DomainEvent {

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var BackerIdentifier */
    private $backer;

    /** @var Coin[] */
    private $coins;
    /** @var string */
    private $promise;

    /**
     * @param CurrencyIdentifier $currency
     * @param BackerIdentifier $backer
     * @param string $promise
     * @param Coin[] $coins
     */
    public function __construct(CurrencyIdentifier $currency, BackerIdentifier $backer, $promise, array $coins) {
        parent::__construct();

        $this->coins = $coins;
        $this->currency = $currency;
        $this->backer = $backer;
        $this->promise = $promise;
    }

    /**
     * @return string
     */
    public function getPromise() {
        return $this->promise;
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
     * @return Coin[]
     */
    public function getCoins() {
        return $this->coins;
    }
}