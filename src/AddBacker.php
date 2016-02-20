<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\app\sourced\messaging\Identifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class AddBacker implements Command {

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var Authentication */
    private $issuer;

    /** @var string */
    private $name;

    /**
     * @param Authentication $issuer
     * @param CurrencyIdentifier $currency
     * @param string $name
     */
    public function __construct(Authentication $issuer, CurrencyIdentifier $currency, $name) {
        $this->currency = $currency;
        $this->issuer = $issuer;
        $this->name = $name;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return Authentication
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @return Identifier
     */
    public function getAggregateIdentifier() {
        return BankIdentifier::singleton();
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}