<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\php\Groupcash;
use groupcash\php\model\signing\Binary;

class Authenticator {

    /** @var Cryptography */
    private $crypto;

    /** @var Groupcash */
    private $lib;

    /**
     * @param Cryptography $crypto
     * @param Groupcash $lib
     */
    public function __construct(Cryptography $crypto, Groupcash $lib) {
        $this->crypto = $crypto;
        $this->lib = $lib;
    }

    /**
     * @param Authentication $authentication
     * @return Binary
     */
    public function getKey(Authentication $authentication) {
        if (!$authentication->getPassword()) {
            return $authentication->getKey();
        }

        return new Binary($this->crypto->decrypt(
            $authentication->getKey()->getData(),
            $authentication->getPassword()));
    }

    public function getAddress(Authentication $authentication) {
        return $this->lib->getAddress($authentication->getKey());
    }
}