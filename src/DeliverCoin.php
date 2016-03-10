<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\model\Identifier;
use groupcash\php\model\Coin;

class DeliverCoin implements Command {

    /** @var AccountIdentifier */
    private $target;

    /** @var Coin */
    private $coin;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param AccountIdentifier $target
     * @param CurrencyIdentifier $currency
     * @param Coin $coin
     */
    public function __construct(AccountIdentifier $target, CurrencyIdentifier $currency, Coin $coin) {
        $this->target = $target;
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
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return Coin
     */
    public function getCoin() {
        return $this->coin;
    }

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return $this->target;
    }
}