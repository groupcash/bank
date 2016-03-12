<?php
namespace groupcash\bank\projecting;

use groupcash\bank\app\Cryptography;
use groupcash\bank\GenerateAccount;
use groupcash\php\Groupcash;
use groupcash\php\model\signing\Binary;

class GeneratedAccount {

    /** @var Binary */
    private $key;

    /** @var Binary */
    private $address;

    /**
     * @param GenerateAccount $c
     * @param Groupcash $library
     * @param Cryptography $crypto
     */
    public function __construct(GenerateAccount $c, Groupcash $library, Cryptography $crypto) {
        $this->key = $library->generateKey();
        $this->address = $library->getAddress($this->key);

        if ($c->getPassword()) {
            $this->key = new Binary($crypto->encrypt($this->key->getData(), $c->getPassword()));
        }
    }

    /**
     * @return Binary
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return Binary
     */
    public function getAddress() {
        return $this->address;
    }
}