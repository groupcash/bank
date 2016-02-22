<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class IssueCoins implements ApplicationCommand {

    /** @var Authentication */
    private $issuer;

    /** @var int|null */
    private $number;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var BackerIdentifier */
    private $backer;

    /**
     * @param Authentication $issuer
     * @param int|null $number
     * @param CurrencyIdentifier $currency
     * @param BackerIdentifier $backer
     */
    public function __construct(Authentication $issuer, $number, CurrencyIdentifier $currency, BackerIdentifier $backer) {
        $this->issuer = $issuer;
        $this->number = $number;
        $this->currency = $currency;
        $this->backer = $backer;
    }

    /**
     * @return Authentication
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @return int|null
     */
    public function getNumber() {
        return $this->number;
    }

    /**
     * @return bool
     */
    public function isAll() {
        return is_null($this->number);
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator) {
        return $this->backer;
    }
}