<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\value\Fraction;
use rtens\domin\parameters\Identifier;

class SendCoins implements Command {

    /** @var Authentication */
    private $owner;

    /** @var AccountIdentifier */
    private $target;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var Fraction */
    private $value;

    /** @var null|string */
    private $subject;

    /**
     * @param Authentication $owner
     * @param AccountIdentifier $target
     * @param CurrencyIdentifier $currency
     * @param Fraction $value
     * @param null|string $subject
     */
    public function __construct(Authentication $owner, AccountIdentifier $target, CurrencyIdentifier $currency,
                                Fraction $value, $subject = null) {
        $this->owner = $owner;
        $this->target = $target;
        $this->currency = $currency;
        $this->value = $value;
        $this->subject = $subject;
    }

    /**
     * @return Authentication
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @return AccountIdentifier
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return Fraction
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return null|string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return AccountIdentifier::fromBinary($auth->getAddress($this->owner));
    }
}