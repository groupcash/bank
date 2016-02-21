<?php
namespace groupcash\bank\model;

class CreatedAccount {

    /** @var string */
    private $address;

    /** @var null|string */
    private $key;

    /**
     * @param string $address
     * @param string $key
     */
    public function __construct($address, $key = null) {
        $this->address = $address;
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @return null|string
     */
    public function getKey() {
        return $this->key;
    }
}