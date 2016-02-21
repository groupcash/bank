<?php
namespace groupcash\bank\projecting;

use groupcash\bank\model\CurrencyIdentifier;

class Currency {

    /** @var CurrencyIdentifier */
    private $address;

    /** @var null|string */
    private $name;

    /**
     * @param CurrencyIdentifier $address
     * @param null|string $name
     */
    public function __construct(CurrencyIdentifier $address, $name = null) {
        $this->address = $address;
        $this->name = $name;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getName() {
        return $this->name ?: substr((string)$this->address, 0, 6);
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }
}