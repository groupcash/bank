<?php
namespace groupcash\bank\app;

use groupcash\bank\model\RandomNumberGenerator;
use groupcash\bank\model\Vault;

class PersistentVault extends Vault {

    private $file;

    public function __construct(RandomNumberGenerator $random, $directory) {
        parent::__construct($random);

        $this->file = $directory . '/secret';
    }


    /**
     * @return boolean
     */
    protected function hasSecret() {
        return file_exists($this->file);
    }

    /**
     * @return string
     */
    protected function readSecret() {
        return file_get_contents($this->file);
    }

    /**
     * @param string $secret
     * @return string
     */
    protected function storeSecret($secret) {
        file_put_contents($this->file, $secret);
    }
}