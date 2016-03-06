<?php
namespace groupcash\bank\model;

use groupcash\php\model\signing\Binary;

class CreatedAccount {

    /** @var Binary */
    private $key;

    /** @var Binary */
    private $address;

    /**
     * @param Binary $key
     * @param Binary $address
     */
    public function __construct(Binary $key, Binary $address) {
        $this->key = $key;
        $this->address = $address;
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