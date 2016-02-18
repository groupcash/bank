<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class PromiseDeclared extends DomainEvent {

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $backer;

    /** @var string */
    private $description;

    /** @var int */
    private $limit;

    /**
     * @param AccountIdentifier $backer
     * @param CurrencyIdentifier $currency
     * @param string $description
     * @param int $limit
     */
    public function __construct(AccountIdentifier $backer, CurrencyIdentifier $currency,
                                $description, $limit) {
        parent::__construct();

        $this->backer = $backer;
        $this->currency = $currency;
        $this->description = $description;
        $this->limit = $limit;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return AccountIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getLimit() {
        return $this->limit;
    }
}