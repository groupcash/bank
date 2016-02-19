<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\CurrencyIdentifier;

class CurrencyRegistered extends DomainEvent {

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var string */
    private $name;

    /**
     * @param CurrencyIdentifier $currency
     * @param string $name
     */
    public function __construct(CurrencyIdentifier $currency, $name) {
        parent::__construct();

        $this->currency = $currency;
        $this->name = $name;
    }

    /**
     * @return CurrencyIdentifier
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
}