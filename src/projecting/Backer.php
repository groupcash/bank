<?php
namespace groupcash\bank\projecting;

use groupcash\bank\model\BackerIdentifier;

class Backer {

    /** @var BackerIdentifier */
    private $address;

    /** @var string */
    private $name;

    /**
     * @param BackerIdentifier $address
     * @param string $name
     */
    public function __construct(BackerIdentifier $address, $name) {
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