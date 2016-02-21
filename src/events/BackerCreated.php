<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class BackerCreated extends DomainEvent {

    /** @var BackerIdentifier */
    private $backer;

    /** @var string */
    private $backerKey;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var string */
    private $name;

    /**
     * @param CurrencyIdentifier $currency
     * @param BackerIdentifier $backer
     * @param string $backerKey
     * @param string $name
     */
    public function __construct(CurrencyIdentifier $currency, BackerIdentifier $backer, $backerKey, $name) {
        parent::__construct();

        $this->backer = $backer;
        $this->backerKey = $backerKey;
        $this->currency = $currency;
        $this->name = $name;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @return string
     */
    public function getBackerKey() {
        return $this->backerKey;
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