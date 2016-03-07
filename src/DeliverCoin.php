<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\php\model\Coin;

class DeliverCoin implements ApplicationCommand {

    /** @var AccountIdentifier */
    private $target;

    /** @var Coin */
    private $coin;

    /**
     * @param AccountIdentifier $target
     * @param Coin $coin
     */
    public function __construct(AccountIdentifier $target, Coin $coin) {
        $this->target = $target;
        $this->coin = $coin;
    }

    /**
     * @return AccountIdentifier
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @return Coin
     */
    public function getCoin() {
        return $this->coin;
    }

    /**
     * @param Authenticator $auth
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return $this->target;
    }
}