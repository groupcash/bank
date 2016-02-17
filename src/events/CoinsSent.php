<?php
namespace groupcash\bank\events;

use groupcash\bank\app\Event;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class CoinsSent extends Event {

    /** @var SentCoin[] */
    private $sentCoins;

    /** @var AccountIdentifier */
    private $owner;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $target;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $owner
     * @param AccountIdentifier $target
     * @param SentCoin[] $sentCoins
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $owner, AccountIdentifier $target, $sentCoins) {
        parent::__construct();

        $this->sentCoins = $sentCoins;
        $this->owner = $owner;
        $this->currency = $currency;
        $this->target = $target;
    }

    /**
     * @return SentCoin[]
     */
    public function getSentCoins() {
        return $this->sentCoins;
    }

    /**
     * @return AccountIdentifier
     */
    public function getOwner() {
        return $this->owner;
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
    public function getTarget() {
        return $this->target;
    }
}