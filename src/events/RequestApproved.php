<?php
namespace groupcash\bank\events;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\value\Fraction;

class RequestApproved extends DomainEvent {

    /** @var AccountIdentifier */
    private $issuer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $target;

    /** @var Fraction[] indexed by backer identifier */
    private $contributions = [];

    /**
     * @param AccountIdentifier $issuer
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $target
     */
    public function __construct(AccountIdentifier $issuer, CurrencyIdentifier $currency, AccountIdentifier $target) {
        parent::__construct();
        $this->currency = $currency;
        $this->target = $target;
        $this->issuer = $issuer;
    }

    /**
     * @param BackerIdentifier $backer
     * @param Fraction $value
     * @return $this
     */
    public function addContribution(BackerIdentifier $backer, Fraction $value) {
        $this->contributions[(string)$backer] = $value;
        return $this;
    }

    /**
     * @return AccountIdentifier
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
     * @return AccountIdentifier
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @return BackerIdentifier
     */
    public function getContributors() {
        return array_map(function ($backer) {
            return new BackerIdentifier($backer);
        }, array_keys($this->contributions));
    }

    /**
     * @param BackerIdentifier $backer
     * @return Fraction
     */
    public function getContribution(BackerIdentifier $backer) {
        return $this->contributions[(string)$backer];
    }
}