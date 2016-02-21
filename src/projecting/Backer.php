<?php
namespace groupcash\bank\projecting;

use groupcash\bank\model\BackerIdentifier;

class Backer {

    /** @var BackerIdentifier */
    private $address;

    /** @var string */
    private $name;

    /** @var Currency[] */
    private $currencies;

    /**
     * @param Currency[] $currencies
     * @param BackerIdentifier $address
     * @param string $name
     */
    public function __construct(array $currencies, BackerIdentifier $address, $name) {
        $this->currencies = $currencies;
        $this->address = $address;
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getCurrencies() {
        return $this->currencies;
    }
}