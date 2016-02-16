<?php
namespace groupcash\bank;

class CreateAccount {

    /** @var string|null */
    private $password;

    /**
     * @param null|string $password
     */
    public function __construct($password) {
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    public function getPassword() {
        return $this->password;
    }
}