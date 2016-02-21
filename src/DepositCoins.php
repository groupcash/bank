<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\app\sourced\messaging\Identifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\BankIdentifier;
use groupcash\php\model\Coin;

class DepositCoins implements Command {

    /** @var Coin[] */
    private $coins;

    /** @var Authentication */
    private $account;

    /**
     * @param Authentication $account
     * @param Coin[] $coins
     */
    public function __construct(Authentication $account, array $coins) {
        $this->coins = $coins;
        $this->account = $account;
    }

    /**
     * @return Authentication
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
     * @return Identifier
     */
    public function getAggregateIdentifier() {
        return BankIdentifier::singleton();
    }
}