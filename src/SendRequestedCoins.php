<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\model\Identifier;
use groupcash\php\model\value\Fraction;

class SendRequestedCoins implements Command {

    /** @var BackerIdentifier */
    private $backer;

    /** @var Fraction */
    private $value;
    /** @var AccountIdentifier */
    private $target;
    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param BackerIdentifier $backer
     * @param Fraction $value
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $target
     */
    public function __construct(BackerIdentifier $backer, Fraction $value, CurrencyIdentifier $currency, AccountIdentifier $target) {
        $this->backer = $backer;
        $this->value = $value;
        $this->target = $target;
        $this->currency = $currency;
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
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
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
        return $this->backer;
    }
}