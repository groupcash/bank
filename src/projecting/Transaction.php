<?php
namespace groupcash\bank\projecting;

use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Fraction;

class Transaction {

    /** @var Fraction */
    private $amount;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var \DateTimeImmutable */
    private $when;

    /**
     * @param \DateTimeImmutable $when
     * @param CurrencyIdentifier $currency
     * @param Fraction $amount
     */
    public function __construct(\DateTimeImmutable $when, CurrencyIdentifier $currency, Fraction $amount) {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->when = $when;
    }

    /**
     * @return Fraction
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getWhen() {
        return $this->when;
    }
}