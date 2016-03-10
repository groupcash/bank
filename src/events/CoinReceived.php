<?php
namespace groupcash\bank\events;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Coin;

class CoinReceived extends DomainEvent {

    /** @var AccountIdentifier */
    private $target;

    /** @var Coin */
    private $coin;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param AccountIdentifier $owner
     * @param CurrencyIdentifier $currency
     * @param Coin $coin
     */
    public function __construct(AccountIdentifier $owner, CurrencyIdentifier $currency, Coin $coin) {
        parent::__construct();
        $this->target = $owner;
        $this->coin = $coin;
        $this->currency = $currency;
    }

    /**
     * @return AccountIdentifier
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @return Coin
     */
    public function getCoin() {
        return $this->coin;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

}