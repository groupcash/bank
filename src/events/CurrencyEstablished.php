<?php
namespace groupcash\bank\events;

use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\RuleBook;

class CurrencyEstablished extends DomainEvent {

    /** @var RuleBook */
    private $rules;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param CurrencyIdentifier $currency
     * @param RuleBook $rules
     */
    public function __construct(CurrencyIdentifier $currency, RuleBook $rules) {
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
     * @return RuleBook
     */
    public function getRules() {
        return $this->rules;
    }
}