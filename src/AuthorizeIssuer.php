<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\signing\Binary;

class AuthorizeIssuer implements ApplicationCommand {

    /** @var Authentication */
    private $currency;

    /** @var Binary */
    private $issuer;

    /**
     * @param Authentication $currency
     * @param Binary $issuer
     */
    public function __construct(Authentication $currency, Binary $issuer) {
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
     * @return Binary
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @param Authenticator $auth
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return new CurrencyIdentifier((string)$auth->getAddress($this->currency));
    }
}