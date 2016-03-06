<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BankIdentifier;

class RegisterCurrency implements ApplicationCommand {

    /** @var Authentication */
    private $currency;

    /** @var string */
    private $name;

    /**
     * @param Authentication $currency
     * @param string $name
     */
    public function __construct(Authentication $currency, $name) {
        $this->currency = $currency;
        $this->name = $name;
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
    public function getName() {
        return $this->name;
    }

    /**
     * @param Authenticator $auth
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return BankIdentifier::singleton();
    }
}