<?php
namespace groupcash\bank\model;

use groupcash\bank\events\BackerKeyStored;
use groupcash\bank\SendCoins;
use groupcash\bank\SendRequestedCoins;
use groupcash\bank\StoreBackerKey;
use groupcash\php\model\signing\Binary;

class Backer extends Account {

    /** @var null|Binary */
    private $key;

    public function handleStoreBackerKey(StoreBackerKey $c) {
        return new BackerKeyStored($c->getKey());
    }

    public function applyBackerKeyStored(BackerKeyStored $e) {
        $this->key = $e->getKey();
    }

    public function handleSendRequestedCoins(SendRequestedCoins $c) {
        return $this->handleSendCoins(new SendCoins(
            new Authentication($this->key),
            $c->getTarget(),
            $c->getCurrency(),
            $c->getValue()
        ));
    }
}