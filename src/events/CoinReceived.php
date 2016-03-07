<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;
use groupcash\php\model\Coin;

class CoinReceived extends DomainEvent {

    /** @var AccountIdentifier */
    private $target;

    /** @var Coin */
    private $coin;

    /**
     * @param AccountIdentifier $owner
     * @param Coin $coin
     */
    public function __construct(AccountIdentifier $owner, Coin $coin) {
        parent::__construct();
        $this->target = $owner;
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

}