<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BankIdentifier;

class RegisterCurrency implements ApplicationCommand {

    /** @var AccountIdentifier */
    private $currency;

    /** @var string */
    private $name;

    /**
     * @param AccountIdentifier $currency
     * @param string $name
     * @throws \Exception
     */
    public function __construct(AccountIdentifier $currency, $name) {
        $this->currency = $currency;
        $this->name = trim($name);

        if (!$this->name) {
            throw new \Exception('The currency name cannot be empty.');
        }
    }

    /**
     * @return AccountIdentifier
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
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator) {
        return BankIdentifier::singleton();
    }
}