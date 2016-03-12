<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\model\Identifier;
use groupcash\php\model\value\Fraction;

class RequestCoins implements Command {

    /** @var Authentication */
    private $account;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var Fraction */
    private $value;

    /**
     * @param Authentication $account
     * @param CurrencyIdentifier $currency
     * @param Fraction $value
     */
    public function __construct(Authentication $account, CurrencyIdentifier $currency, Fraction $value) {
        $this->account = $account;
        $this->currency = $currency;
        $this->value = $value;
    }

    /**
     * @return Authentication
     */
    public function getAccount() {
        return $this->account;
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
        return $this->currency;
    }
}