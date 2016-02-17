<?php
namespace groupcash\bank;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Coin;

class DeliverCoins {

    /** @var Coin[] */
    private $coins;

    /** @var AccountIdentifier */
    private $target;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $target
     * @param Coin[] $coins
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $target, array $coins) {
        $this->coins = $coins;
        $this->target = $target;
        $this->currency = $currency;
    }

    /**
     * @return Coin[]
     */
    public function getCoins() {
        return $this->coins;
    }

    /**
     * @return AccountIdentifier
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }
}