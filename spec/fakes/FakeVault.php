<?php
namespace spec\groupcash\bank\fakes;

use groupcash\bank\model\Vault;

class FakeVault extends Vault {

    private $secret;

    /**
     * @return boolean
     */
    protected function hasSecret() {
        return !!$this->secret;
    }

    /**
     * @return string
     */
    protected function readSecret() {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return string
     */
    protected function storeSecret($secret) {
        $this->secret = $secret;
    }
}