<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\php\model\signing\Binary;

class Authenticator {

    /** @var Cryptography */
    private $crypto;

    /**
     * @param Cryptography $crypto
     */
    public function __construct(Cryptography $crypto) {
        $this->crypto = $crypto;
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
}