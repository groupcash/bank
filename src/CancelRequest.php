<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\model\Identifier;

class CancelRequest implements Command {

    /** @var Authentication */
    private $issuer;

    /** @var AccountIdentifier */
    private $account;

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param Authentication $issuer
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $account
     */
    public function __construct(Authentication $issuer, CurrencyIdentifier $currency, AccountIdentifier $account) {
        $this->issuer = $issuer;
        $this->account = $account;
        $this->currency = $currency;
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
     * @return AccountIdentifier
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return $this->currency;
    }
}