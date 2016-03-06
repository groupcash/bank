<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\CreateAccount;
use groupcash\bank\EstablishCurrency;
use groupcash\bank\events\AccountCreated;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\php\Groupcash;
use groupcash\php\model\signing\Binary;

class Bank extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Cryptography */
    private $crypto;

    /** @var Authenticator */
    private $auth;

    /** @var Binary[] */
    private $establishedCurrencies = [];

    /** @var string[] */
    private $registeredCurrencies = [];

    /**
     * @param Groupcash $lib
     * @param Cryptography $crypto
     */
    public function __construct(Groupcash $lib, Cryptography $crypto) {
        $this->lib = $lib;
        $this->crypto = $crypto;
        $this->auth = new Authenticator($crypto);
    }

    protected function handleCreateAccount(CreateAccount $c) {
        $key = $this->lib->generateKey();
        $address = $this->lib->getAddress($key);

        $this->record(new AccountCreated($address));

        if ($c->getPassword()) {
            $key = new Binary($this->crypto->encrypt($key->getData(), $c->getPassword()));
        }

        return new CreatedAccount($key, $address);
    }

    protected function handleEstablishCurrency(EstablishCurrency $c) {
        if (!trim($c->getRules())) {
            throw new \Exception("The rules cannot be empty.");
        }

        if (in_array($c->getName(), $this->registeredCurrencies)) {
            throw new \Exception('A currency is already registered under this name.');
        }

        $key = $this->auth->getKey($c->getCurrency());
        $address = $this->lib->getAddress($key);
        if (in_array($address, $this->establishedCurrencies)) {
            throw new \Exception("This currency is already established.");
        }

        $rules = $this->lib->signCurrencyRules($key, $c->getRules());
        $this->record(new CurrencyEstablished($rules));

        if ($c->getName()) {
            $this->record(new CurrencyRegistered($address, $c->getName()));
        }
    }

    protected function applyCurrencyEstablished(CurrencyEstablished $e) {
        $this->establishedCurrencies[] = $e->getRules()->getCurrencyAddress();
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->registeredCurrencies[] = $e->getName();
    }
}