<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class CoinsSent extends DomainEvent {

    /** @var TransferredCoin[] */
    private $sentCoins;

    /** @var AccountIdentifier */
    private $owner;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $target;

    /** @var null|string */
    private $subject;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $account
     * @param AccountIdentifier $target
     * @param TransferredCoin[] $sentCoins
     * @param null|string $subject
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $account, AccountIdentifier $target,
                                $sentCoins, $subject = null) {
        parent::__construct();

        $this->sentCoins = $sentCoins;
        $this->owner = $account;
        $this->currency = $currency;
        $this->target = $target;
        $this->subject = $subject;
    }

    /**
     * @return TransferredCoin[]
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

    /**
     * @return null|string
     */
    public function getSubject() {
        return $this->subject;
    }
}