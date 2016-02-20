<?php
namespace groupcash\bank\projecting;

use groupcash\bank\model\AccountIdentifier;

class Backer {

    /** @var AccountIdentifier */
    private $address;

    /** @var string */
    private $name;

    /**
     * @param AccountIdentifier $address
     * @param string $name
     */
    public function __construct(AccountIdentifier $address, $name) {
        $this->address = $address;
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function getAddress() {
        return $this->address;
    }
}