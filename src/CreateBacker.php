<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\model\Identifier;

class CreateBacker implements Command {

    /** @var Authentication */
    private $issuer;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param Authentication $issuer
     * @param CurrencyIdentifier $currency
     */
    public function __construct(Authentication $issuer, CurrencyIdentifier $currency) {
        $this->issuer = $issuer;
        $this->currency = $currency;
    }

    /**
     * @return Authentication
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return $this->currency;
    }
}