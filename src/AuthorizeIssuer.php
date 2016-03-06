<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;

class AuthorizeIssuer implements ApplicationCommand {

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

    /**
     * @param Authenticator $auth
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return new CurrencyIdentifier((string)$auth->getAddress($this->currency));
    }
}