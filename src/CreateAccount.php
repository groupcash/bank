<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Command;

class CreateAccount implements Command {

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