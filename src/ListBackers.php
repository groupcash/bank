<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Query;
use groupcash\bank\model\CurrencyIdentifier;

class ListBackers implements Query {

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param CurrencyIdentifier $currency
     */
    public function __construct(CurrencyIdentifier $currency) {
        $this->currency = $currency;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }
}