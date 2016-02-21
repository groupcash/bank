<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class CoinsWithdrawn extends DomainEvent {

    /** @var AccountIdentifier */
    private $account;

    /** @var array|TransferredCoin[] */
    private $coins;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $account
     * @param TransferredCoin[] $coins
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $account, array $coins) {
        parent::__construct();
        $this->account = $account;
        $this->coins = $coins;
        $this->currency = $currency;
    }

    /**
     * @return AccountIdentifier
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * @return array|TransferredCoin[]
     */
    public function getCoins() {
        return $this->coins;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }
}