<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class AddCurrencyToBacker implements ApplicationCommand {

    /** @var BackerIdentifier */
    private $backer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $issuer;

    /**
     * @param BackerIdentifier $backer
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $issuer
     */
    public function __construct(BackerIdentifier $backer, CurrencyIdentifier $currency, AccountIdentifier $issuer) {
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
     * @return AccountIdentifier
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator) {
        return $this->backer;
    }
}