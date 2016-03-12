<?php
namespace groupcash\bank;

class GenerateAccount {

    /** @var null|string */
    private $password;

    /**
     * @param null|string $password
     */
    public function __construct($password = null) {
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    public function getPassword() {
        return $this->password;
    }
}