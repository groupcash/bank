<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;

class EstablishCurrency implements ApplicationCommand {

    /** @var Authentication */
    private $currency;

    /** @var string */
    private $rules;

    /**
     * @param Authentication $currency
     * @param string $rules
     */
    public function __construct(Authentication $currency, $rules) {
        $this->currency = $currency;
        $this->rules = $rules;
    }

    /**
     * @return Authentication
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getRules() {
        return $this->rules;
    }

    /**
     * @param Authenticator $auth
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return CurrencyIdentifier::fromBinary($auth->getAddress($this->currency));
    }
}