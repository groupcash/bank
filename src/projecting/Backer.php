<?php
namespace groupcash\bank\projecting;

use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class Backer {

    /** @var BackerIdentifier */
    private $address;

    /** @var string */
    private $name;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var string */
    private $currencyName;

    /**
     * @param CurrencyIdentifier $currency
     * @param string $currencyName
     * @param BackerIdentifier $address
     * @param string $name
     */
    public function __construct(CurrencyIdentifier $currency, $currencyName, BackerIdentifier $address, $name) {
        $this->currency = $currency;
        $this->address = $address;
        $this->name = $name;
        $this->currencyName = $currencyName;
    }

    public function getName() {
        return $this->name;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function getCurrencyName() {
        return $this->currencyName;
    }
}