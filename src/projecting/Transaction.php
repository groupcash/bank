<?php
namespace groupcash\bank\projecting;

use groupcash\php\model\Fraction;

class Transaction {

    /** @var Fraction */
    private $amount;

    /** @var Currency */
    private $currency;

    /** @var \DateTimeImmutable */
    private $when;

    /** @var null|string */
    private $subject;

    /**
     * @param \DateTimeImmutable $when
     * @param Currency $currency
     * @param Fraction $amount
     * @param null|string $subject
     */
    public function __construct(\DateTimeImmutable $when, Currency $currency, Fraction $amount, $subject = null) {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->when = $when;
        $this->subject = $subject;
    }

    /**
     * @return Fraction
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @return Currency
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

    /**
     * @return null|string
     */
    public function getSubject() {
        return $this->subject;
    }
}