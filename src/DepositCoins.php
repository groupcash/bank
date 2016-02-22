<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\php\model\Coin;

class DepositCoins implements ApplicationCommand {

    /** @var Coin[] */
    private $coins;

    /** @var AccountIdentifier */
    private $account;

    /**
     * @param AccountIdentifier $account
     * @param Coin[] $coins
     */
    public function __construct(AccountIdentifier $account, array $coins) {
        $this->coins = $coins;
        $this->account = $account;
    }

    /**
     * @return AccountIdentifier
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * @return Coin[]
     */
    public function getCoins() {
        return $this->coins;
    }

    /**
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator) {
        return $this->account;
    }
}