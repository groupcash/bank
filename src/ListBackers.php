<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Query;
use groupcash\bank\model\CurrencyIdentifier;

class ListBackers implements Query {

    /** @var null|CurrencyIdentifier */
    private $currency;

    /**
     * @param null|CurrencyIdentifier $currency
     */
    public function __construct(CurrencyIdentifier $currency = null) {
        $this->currency = $currency;
    }

    /**
     * @return null|CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }
}