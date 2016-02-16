<?php
namespace groupcash\bank;

use groupcash\bank\model\Authentication;
use groupcash\bank\model\CurrencyIdentifier;

class AddBacker {

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var Authentication */
    private $issuer;

    /**
     * @param Authentication $issuer
     * @param CurrencyIdentifier $currency
     */
    public function __construct(Authentication $issuer, CurrencyIdentifier $currency) {
        $this->currency = $currency;
        $this->issuer = $issuer;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return Authentication
     */
    public function getIssuer() {
        return $this->issuer;
    }
}