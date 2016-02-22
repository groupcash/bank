<?php
namespace groupcash\bank\model;

use groupcash\bank\AddBackerToCurrency;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\events\BackerAdded;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\php\Groupcash;

class Currency extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Authenticator */
    private $auth;

    /** @var AccountIdentifier[] */
    private $issuers = [];

    /** @var BackerIdentifier[] */
    private $backers = [];

    /**
     * @param Groupcash $lib
     * @param Authenticator $auth
     */
    public function __construct(Groupcash $lib, Authenticator $auth) {
        $this->lib = $lib;
        $this->auth = $auth;
    }

    protected function handleAuthorizeIssuer(AuthorizeIssuer $c) {
        $currencyKey = $this->auth->getKey($c->getCurrency());
        $currency = new CurrencyIdentifier($this->lib->getAddress($currencyKey));

        if (in_array($c->getIssuer(), $this->issuers)) {
            throw new \Exception('This issuer is already authorized for this currency.');
        }

        $this->record(new IssuerAuthorized(
            $currency,
            $this->lib->authorizeIssuer($currencyKey, (string)$c->getIssuer())
        ));
    }

    protected function applyIssuerAuthorized(IssuerAuthorized $e) {
        $this->issuers[] = new AccountIdentifier($e->getAuthorization()->getIssuer());
    }

    protected function handleAddBackerToCurrency(AddBackerToCurrency $c) {
        $issuer = new AccountIdentifier($this->auth->getAddress($c->getIssuer()));

        if (!in_array($issuer, $this->issuers)) {
            throw new \Exception('This is not an issuer of this currency.');
        }

        if (in_array($c->getBacker(), $this->backers)) {
            throw new \Exception('This backer was already added to this currency.');
        }

        $this->record(new BackerAdded(
            $issuer,
            $c->getCurrency(),
            $c->getBacker()
        ));
    }

    protected function applyBackerAdded(BackerAdded $e) {
        $this->backers[] = $e->getBacker();
    }
}