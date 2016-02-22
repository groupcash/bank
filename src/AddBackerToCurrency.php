<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class AddBackerToCurrency implements ApplicationCommand {

    /** @var BackerIdentifier */
    private $backer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var Authentication */
    private $issuer;

    /**
     * @param Authentication $issuer
     * @param CurrencyIdentifier $currency
     * @param BackerIdentifier $backer
     */
    public function __construct(Authentication $issuer, CurrencyIdentifier $currency, BackerIdentifier $backer) {
        $this->backer = $backer;
        $this->currency = $currency;
        $this->issuer = $issuer;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return Authentication
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator) {
        return $this->currency;
    }
}