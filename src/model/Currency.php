<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\EstablishCurrency;
use groupcash\bank\events\CoinIssued;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\IssueCoin;
use groupcash\php\Groupcash;
use groupcash\php\model\Output;

class Currency extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Cryptography */
    private $crypto;

    /** @var Authenticator */
    private $auth;

    /** @var bool */
    private $established = false;

    /** @var AccountIdentifier[] */
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

        $currency = CurrencyIdentifier::fromBinary($this->lib->getAddress($key));
        $rules = $this->lib->signRules($key, $c->getRules());
        $this->record(new CurrencyEstablished($currency, $rules));
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

        $currencyKey = $this->auth->getKey($c->getCurrency());
        $currency = CurrencyIdentifier::fromBinary($this->lib->getAddress($currencyKey));
        $issuerAddress = $c->getIssuer()->toBinary();
        $issuer = AccountIdentifier::fromBinary($issuerAddress);

        $authorization = $this->lib->authorizeIssuer($currencyKey, $issuerAddress);
        $this->record(new IssuerAuthorized($currency, $issuer, $authorization));
    }

    protected function applyIssuerAuthorized(IssuerAuthorized $e) {
        $this->authorizedIssuers[] = $e->getIssuer();
    }

    protected function handleIssueCoin(IssueCoin $c) {
        if (!trim($c->getDescription())) {
            throw new \Exception('The description cannot be empty.');
        }

        $issuerKey = $this->auth->getKey($c->getIssuer());
        $issuer = AccountIdentifier::fromBinary($this->lib->getAddress($issuerKey));

        if (!in_array($issuer, $this->authorizedIssuers)) {
            throw new \Exception('Not authorized to issue this currency.');
        }

        $this->record(new CoinIssued(
            $c->getCurrency(),
            $issuer,
            $c->getBacker(),
            $this->lib->issueCoin(
                $issuerKey,
                $c->getCurrency()->toBinary(),
                $c->getDescription(),
                new Output(
                    $c->getBacker()->toBinary(),
                    $c->getValue()
                ))
        ));
    }
}