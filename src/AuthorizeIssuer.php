<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;

class AuthorizeIssuer implements Command {

    /** @var Authentication */
    private $currency;

    /** @var AccountIdentifier */
    private $issuer;

    /**
     * @param Authentication $currency
     * @param AccountIdentifier $issuer
     */
    public function __construct(Authentication $currency, AccountIdentifier $issuer) {
        $this->currency = $currency;
        $this->issuer = $issuer;
    }

    /**
     * @return Authentication
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return AccountIdentifier
     */
    public function getIssuer() {
        return $this->issuer;
    }
}