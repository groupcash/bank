<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\EstablishCurrency;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\php\Groupcash;
use groupcash\php\model\signing\Binary;

class Currency extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Cryptography */
    private $crypto;

    /** @var Authenticator */
    private $auth;

    /** @var bool */
    private $established = false;

    /** @var Binary[] */
    private $authorizedIssuers = [];

    /**
     * @param Groupcash $lib
     * @param Cryptography $crypto
     */
    public function __construct(Groupcash $lib, Cryptography $crypto) {
        $this->lib = $lib;
        $this->crypto = $crypto;
        $this->auth = new Authenticator($crypto, $lib);
    }

    protected function handleEstablishCurrency(EstablishCurrency $c) {
        if (!trim($c->getRules())) {
            throw new \Exception("The rules cannot be empty.");
        }

        $key = $this->auth->getKey($c->getCurrency());
        if ($this->established) {
            throw new \Exception("This currency is already established.");
        }

        $rules = $this->lib->signCurrencyRules($key, $c->getRules());
        $this->record(new CurrencyEstablished($rules));
    }

    protected function applyCurrencyEstablished() {
        $this->established = true;
    }

    protected function handleAuthorizeIssuer(AuthorizeIssuer $c) {
        if (!$this->established) {
            throw new \Exception('Not an established currency.');
        }

        if (in_array($c->getIssuer(), $this->authorizedIssuers)) {
            throw new \Exception('This issuer is already authorized.');
        }

        $authorization = $this->lib->authorizeIssuer($this->auth->getKey($c->getCurrency()), $c->getIssuer());
        $this->record(new IssuerAuthorized($authorization));
    }

    protected function applyIssuerAuthorized(IssuerAuthorized $e) {
        $this->authorizedIssuers[] = $e->getAuthorization()->getIssuerAddress();
    }
}