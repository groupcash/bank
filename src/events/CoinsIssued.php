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

    /**
     * @param CurrencyIdentifier $currency
     * @param BackerIdentifier $backer
     * @param Coin[] $coins
     */
    public function __construct(CurrencyIdentifier $currency, BackerIdentifier $backer, array $coins) {
        parent::__construct();

        $this->coins = $coins;
        $this->currency = $currency;
        $this->backer = $backer;
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