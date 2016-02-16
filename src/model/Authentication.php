<?php
namespace groupcash\bank\model;

class Authentication {

    /** @var string */
    private $key;

    /** @var string|null */
    private $password;

    /**
     * @param string $key
     * @param string|null $password
     */
    public function __construct($key, $password = null) {
        $this->key = $key;
        $this->password = $password;
    }

    /**
     * @return string
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