<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\bank\events\AccountCreated;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\CreateAccount;
use groupcash\php\Groupcash;
use groupcash\php\model\signing\Binary;

class Bank extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Cryptography */
    private $crypto;

    /**
     * @param Groupcash $lib
     * @param Cryptography $crypto
     */
    public function __construct(Groupcash $lib, Cryptography $crypto) {
        $this->lib = $lib;
        $this->crypto = $crypto;
    }

    protected function handleCreateAccount(CreateAccount $c) {
        $key = $this->lib->generateKey();
        $address = $this->lib->getAddress($key);

        $this->record(new AccountCreated($address));

        if ($c->getPassword()) {
            $key = new Binary($this->crypto->encrypt($key->getData(), $c->getPassword()));
        }

        return new CreatedAccount($key, $address);
    }
}