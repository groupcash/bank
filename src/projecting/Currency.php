<?php
namespace groupcash\bank\projecting;

use groupcash\bank\model\CurrencyIdentifier;

class Currency {

    /** @var CurrencyIdentifier */
    private $address;

    /** @var string */
    private $name;

    /**
     * @param CurrencyIdentifier $address
     * @param string $name
     */
    public function __construct(CurrencyIdentifier $address, $name) {
        $this->address = $address;
        $this->name = $name;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getName() {
        return $this->name;
    }
}