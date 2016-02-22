<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Fraction;

class SendCoins implements ApplicationCommand {

    /** @var Authentication */
    private $owner;

    /** @var Fraction */
    private $amount;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $target;

    /** @var null|string */
    private $subject;

    /**
     * @param Authentication $owner
     * @param Fraction $amount
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $target
     * @param null|string $subject
     */
    public function __construct(Authentication $owner, Fraction $amount, CurrencyIdentifier $currency,
                                AccountIdentifier $target, $subject = null) {
        $this->owner = $owner;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->target = $target;
        $this->subject = $subject;
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
     * @return AccountIdentifier
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @return null|string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator) {
        return new AccountIdentifier($authenticator->getAddress($this->owner));
    }
}