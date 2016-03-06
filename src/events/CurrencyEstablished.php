<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\php\model\CurrencyRules;

class CurrencyEstablished extends DomainEvent {

    /** @var CurrencyRules */
    private $rules;

    /**
     * @param CurrencyRules $rules
     */
    public function __construct(CurrencyRules $rules) {
        parent::__construct();

        $this->rules = $rules;
    }

    /**
     * @return CurrencyRules
     */
    public function getRules() {
        return $this->rules;
    }
}