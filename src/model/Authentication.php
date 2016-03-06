<?php
namespace groupcash\bank\model;

use groupcash\php\model\signing\Binary;

class Authentication {

    /** @var Binary */
    private $key;

    /** @var null|string */
    private $password;

    /**
     * @param Binary $key
     * @param null|string $password
     */
    public function __construct(Binary $key, $password = null) {
        $this->key = $key;
        $this->password = $password;
    }

    /**
     * @return Binary
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return null|string
     */
    public function getPassword() {
        return $this->password;
    }
}