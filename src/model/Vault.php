<?php
namespace groupcash\bank\model;

abstract class Vault {

    /** @var RandomNumberGenerator */
    private $random;

    /**
     * @param RandomNumberGenerator $random
     */
    public function __construct(RandomNumberGenerator $random) {
        $this->random = $random;
    }

    public function getSecret() {
        if ($this->hasSecret()) {
            return $this->readSecret();
        }

        $secret = $this->random->generate();
        $this->storeSecret($secret);
        return $secret;
    }

    /**
     * @return boolean
     */
    abstract protected function hasSecret();

    /**
     * @return string
     */
    abstract protected function readSecret();

    /**
     * @param string $secret
     * @return string
     */
    abstract protected function storeSecret($secret);
}