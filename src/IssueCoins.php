<?php
namespace groupcash\bank;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\CurrencyIdentifier;

class IssueCoins {

    /** @var Authentication */
    private $issuer;

    /** @var int|null */
    private $number;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var AccountIdentifier */
    private $backer;

    /**
     * @param Authentication $issuer
     * @param int|null $number
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $backer
     */
    public function __construct(Authentication $issuer, $number, CurrencyIdentifier $currency, AccountIdentifier $backer) {
        $this->issuer = $issuer;
        $this->number = $number;
        $this->currency = $currency;
        $this->backer = $backer;
    }

    /**
     * @return |Authentication
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
     * @return AccountIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }
}