<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\bank\CreateBacker;
use groupcash\bank\events\AccountRegistered;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\BackerDetailsChanged;
use groupcash\bank\events\BackerRegistered;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\RegisterAccount;
use groupcash\bank\RegisterCurrency;
use groupcash\php\Groupcash;

class Bank {

    /** @var Groupcash */
    private $lib;

    /** @var Cryptography */
    private $crypto;

    /** @var Authenticator */
    private $auth;

    /** @var string[] */
    private $registeredCurrencies = [];

    /** @var string[] */
    private $registeredBackers = [];

    /**
     * @param Groupcash $lib
     * @param Cryptography $crypto
     */
    public function __construct(Groupcash $lib, Cryptography $crypto) {
        $this->lib = $lib;
        $this->crypto = $crypto;
        $this->auth = new Authenticator($crypto, $lib);
    }

    public function handleRegisterAccount(RegisterAccount $c) {
        return new AccountRegistered(AccountIdentifier::fromBinary($c->getAddress()));
    }

    public function handleRegisterCurrency(RegisterCurrency $c) {
        if (!trim($c->getName())) {
            throw new \Exception('The name cannot be empty.');
        }

        if (in_array($c->getName(), $this->registeredCurrencies)) {
            throw new \Exception('A currency is already registered under this name.');
        }

        $currency = CurrencyIdentifier::fromBinary($this->auth->getAddress($c->getCurrency()));

        return new CurrencyRegistered($currency, $c->getName());
    }

    public function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->registeredCurrencies[] = $e->getName();
    }

    public function handleCreateBacker(CreateBacker $c) {
        $key = $this->lib->generateKey();
        $backer = BackerIdentifier::fromBinary($this->lib->getAddress($key));

        $events = [];
        $events[] = new BackerCreated($backer, $key);

        if ($c->getName()) {
            if (in_array($c->getName(), $this->registeredBackers)) {
                throw new \Exception('A backer with this name is already registered.');
            }
            $events[] = new BackerRegistered($backer, $c->getName());
        }

        if ($c->getDetails()) {
            $events[] = new BackerDetailsChanged($backer, $c->getDetails());
        }

        return $events;
    }

    public function applyBackerRegistered(BackerRegistered $e) {
        $this->registeredBackers[] = $e->getName();
    }
}