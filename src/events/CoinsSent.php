<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Coin;

class CoinsSent extends DomainEvent {

    /** @var AccountIdentifier */
    private $owner;

    /** @var AccountIdentifier */
    private $target;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var Coin */
    private $transferred;

    /** @var Coin[] */
    private $coins;

    /**
     * @param AccountIdentifier $owner
     * @param AccountIdentifier $target
     * @param CurrencyIdentifier $currency
     * @param Coin[] $coins
     * @param Coin $transferred
     */
    public function __construct(AccountIdentifier $owner, AccountIdentifier $target, CurrencyIdentifier $currency, array $coins, Coin $transferred) {
        parent::__construct();
        $this->owner = $owner;
        $this->target = $target;
        $this->currency = $currency;
        $this->coins = $coins;
        $this->transferred = $transferred;
    }

    /**
     * @return AccountIdentifier
     */
    public function getOwner() {
        return $this->owner;
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

    /**
     * @return Coin
     */
    public function getTransferred() {
        return $this->transferred;
    }

    /**
     * @return Coin[]
     */
    public function getCoins() {
        return $this->coins;
    }
}