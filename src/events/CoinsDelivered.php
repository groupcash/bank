<?php
namespace groupcash\bank\events;

use groupcash\bank\app\Event;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Coin;

class CoinsDelivered extends Event {

    /** @var Coin[] */
    private $coins;

    /** @var AccountIdentifier */
    private $target;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var null|string */
    private $subject;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $target
     * @param Coin[] $coins
     * @param null|string $subject
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $target, array $coins, $subject = null) {
        parent::__construct();

        $this->coins = $coins;
        $this->target = $target;
        $this->currency = $currency;
        $this->subject = $subject;
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

    /**
     * @return null|string
     */
    public function getSubject() {
        return $this->subject;
    }
}