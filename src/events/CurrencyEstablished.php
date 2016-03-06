<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\CurrencyRules;

class CurrencyEstablished extends DomainEvent {

    /** @var CurrencyRules */
    private $rules;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param CurrencyIdentifier $currency
     * @param CurrencyRules $rules
     */
    public function __construct(CurrencyIdentifier $currency, CurrencyRules $rules) {
        parent::__construct();

        $this->rules = $rules;
        $this->currency = $currency;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return CurrencyRules
     */
    public function getRules() {
        return $this->rules;
    }
}