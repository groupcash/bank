<?php
namespace groupcash\bank\events;

use groupcash\bank\app\Event;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class BackerAdded extends Event {

    /** @var AccountIdentifier */
    private $backer;

    /** @var string */
    private $backerKey;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $backer
     * @param string $backerKey
     */
    public function __construct(CurrencyIdentifier $currency, AccountIdentifier $backer, $backerKey) {
        parent::__construct();

        $this->backer = $backer;
        $this->backerKey = $backerKey;
        $this->currency = $currency;
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
    public function getBackerKey() {
        return $this->backerKey;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }
}