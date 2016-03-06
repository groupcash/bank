<?php
namespace groupcash\bank\model;

use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\events\AccountCreated;
use groupcash\php\Groupcash;

class Bank extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /**
     * @param Groupcash $lib
     */
    public function __construct(Groupcash $lib) {
        $this->lib = $lib;
    }

    protected function handleCreateAccount() {
        $key = $this->lib->generateKey();
        $address = $this->lib->getAddress($key);

        $this->record(new AccountCreated($address));

        return new CreatedAccount($key, $address);
    }
}