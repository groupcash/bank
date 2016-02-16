<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;

class Authenticator {

    /** @var Cryptography */
    private $crypto;

    /** @var string */
    private $secret;

    /**
     * @param Cryptography $crypto
     * @param string $secret
     */
    public function __construct(Cryptography $crypto, $secret) {
        $this->crypto = $crypto;
        $this->secret = $secret;
    }

    public function encrypt($key, $password = null) {
        if (is_null($password)) {
            return $key;
        }

        return $this->crypto->encrypt($key, $this->secret . $password);
    }

    public function getKey(Authentication $authentication) {
        if (is_null($authentication->getPassword())) {
            return $authentication->getKey();
        }

        return $this->crypto->decrypt($authentication->getKey(), $this->secret . $authentication->getPassword());
    }
}