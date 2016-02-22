<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\php\Groupcash;

class Authenticator {

    /** @var Cryptography */
    private $crypto;

    /** @var Vault */
    private $vault;

    /** @var Groupcash */
    private $lib;

    /**
     * @param Cryptography $crypto
     * @param Vault $vault
     * @param Groupcash $lib
     */
    public function __construct(Cryptography $crypto, Vault $vault, Groupcash $lib) {
        $this->crypto = $crypto;
        $this->vault = $vault;
        $this->lib = $lib;
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

    public function getAddress(Authentication $authentication) {
        return $this->lib->getAddress($this->getKey($authentication));
    }
}