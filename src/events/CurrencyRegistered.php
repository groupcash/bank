<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;

class CurrencyRegistered extends DomainEvent {

    /** @var AccountIdentifier */
    private $currency;

    /** @var string */
    private $name;

    /**
     * @param AccountIdentifier $currency
     * @param string $name
     */
    public function __construct(AccountIdentifier $currency, $name) {
        parent::__construct();

        $this->currency = $currency;
        $this->name = $name;
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
}