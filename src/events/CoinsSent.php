<?php
namespace groupcash\bank\events;

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

    /** @var null|string */
    private $subject;

    /**
     * @param AccountIdentifier $owner
     * @param AccountIdentifier $target
     * @param CurrencyIdentifier $currency
     * @param Coin[] $coins
     * @param Coin $transferred
     * @param null|string $subject
     */
    public function __construct(AccountIdentifier $owner, AccountIdentifier $target, CurrencyIdentifier $currency,
                                array $coins, Coin $transferred, $subject = null) {
        parent::__construct();
        $this->owner = $owner;
        $this->target = $target;
        $this->currency = $currency;
        $this->coins = $coins;
        $this->transferred = $transferred;
        $this->subject = $subject;
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

    /**
     * @return null|string
     */
    public function getSubject() {
        return $this->subject;
    }
}