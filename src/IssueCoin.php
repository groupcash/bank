<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\model\Identifier;
use groupcash\php\model\value\Fraction;

class IssueCoin implements Command {

    /** @var Authentication */
    private $issuer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var string */
    private $description;

    /** @var Fraction */
    private $value;

    /** @var BackerIdentifier */
    private $backer;

    /**
     * @param Authentication $issuer
     * @param CurrencyIdentifier $currency
     * @param string $description
     * @param Fraction $value
     * @param BackerIdentifier $backer
     */
    public function __construct(Authentication $issuer, CurrencyIdentifier $currency, $description, Fraction $value, BackerIdentifier $backer) {
        $this->issuer = $issuer;
        $this->currency = $currency;
        $this->description = $description;
        $this->value = $value;
        $this->backer = $backer;
    }

    /**
     * @return Authentication
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return Fraction
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return $this->currency;
    }
}