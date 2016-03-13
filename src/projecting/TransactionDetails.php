<?php
namespace groupcash\bank\projecting;

use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\value\Fraction;

class TransactionDetails {

    /** @var \DateTimeImmutable */
    private $when;

    /** @var Fraction */
    private $value;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var null|string */
    private $subject;

    /**
     * @param \DateTimeImmutable $when
     * @param CurrencyIdentifier $currency
     * @param Fraction $value
     * @param null|string $subject
     */
    public function __construct(\DateTimeImmutable $when, Fraction $value, CurrencyIdentifier $currency, $subject = null) {
        $this->value = $value;
        $this->currency = $currency;
        $this->subject = $subject;
        $this->when = $when;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getWhen() {
        return $this->when;
    }

    /**
     * @return Fraction
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return null|string
     */
    public function getSubject() {
        return $this->subject;
    }
}