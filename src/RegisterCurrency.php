<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\Identifier;

class RegisterCurrency implements Command {

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
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return BankIdentifier::singleton();
    }
}