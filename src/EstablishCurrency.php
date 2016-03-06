<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\BankIdentifier;

class EstablishCurrency implements ApplicationCommand {

    /** @var Authentication */
    private $currency;

    /** @var string */
    private $rules;

    /** @var null|string */
    private $name;

    /**
     * @param Authentication $currency
     * @param string $rules
     * @param null|string $name
     */
    public function __construct(Authentication $currency, $rules, $name = null) {
        $this->currency = $currency;
        $this->rules = $rules;
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
    public function getRules() {
        return $this->rules;
    }

    /**
     * @return null|string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier() {
        return BankIdentifier::singleton();
    }
}