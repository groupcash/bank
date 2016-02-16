<?php
namespace groupcash\bank;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Fraction;

class SendCoins {

    /** @var Authentication */
    private $owner;

    /** @var Fraction */
    private $fraction;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $target;

    /**
     * @param Authentication $owner
     * @param int|Fraction $amount
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $target
     */
    public function __construct(Authentication $owner, $amount, CurrencyIdentifier $currency, AccountIdentifier $target) {
        $this->owner = $owner;
        $this->fraction = ($amount instanceof Fraction) ? $amount : new Fraction(intval($amount));
        $this->currency = $currency;
        $this->target = $target;
    }

    /**
     * @return Authentication
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @return Fraction
     */
    public function getFraction() {
        return $this->fraction;
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
    public function getTarget() {
        return $this->target;
    }
}