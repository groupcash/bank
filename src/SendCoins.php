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

    /**
     * @param Authentication $owner
     * @param AccountIdentifier $target
     * @param CurrencyIdentifier $currency
     * @param Fraction $value
     */
    public function __construct(Authentication $owner, AccountIdentifier $target, CurrencyIdentifier $currency, Fraction $value) {
        $this->owner = $owner;
        $this->target = $target;
        $this->currency = $currency;
        $this->value = $value;
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
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return AccountIdentifier::fromBinary($auth->getAddress($this->owner));
    }
}