<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;

class Authenticator {

    /** @var Cryptography */
    private $crypto;

    /** @var Vault */
    private $vault;

    /**
     * @param Cryptography $crypto
     * @param Vault $vault
     */
    public function __construct(Cryptography $crypto, Vault $vault) {
        $this->crypto = $crypto;
        $this->vault = $vault;
    }

    public function encrypt($key, $password = null) {
        if (is_null($password)) {
            return $key;
        }

        return $this->crypto->encrypt($key, $this->vault->getSecret() . $password);
    }

    public function getKey(Authentication $authentication) {
        if (is_null($authentication->getPassword())) {
            return $authentication->getKey();
        }

        return $this->crypto->decrypt($authentication->getKey(), $this->vault->getSecret() . $authentication->getPassword());
    }
}