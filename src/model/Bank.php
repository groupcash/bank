<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\CreateAccount;
use groupcash\bank\CreateBacker;
use groupcash\bank\events\AccountCreated;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\BackerDetailsChanged;
use groupcash\bank\events\BackerRegistered;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\RegisterCurrency;
use groupcash\php\Groupcash;
use groupcash\php\model\signing\Binary;

class Bank extends AggregateRoot {

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

    protected function handleCreateAccount(CreateAccount $c) {
        $key = $this->lib->generateKey();
        $address = $this->lib->getAddress($key);

        $this->record(new AccountCreated(AccountIdentifier::fromBinary($address)));

        if ($c->getPassword()) {
            $key = new Binary($this->crypto->encrypt($key->getData(), $c->getPassword()));
        }

        return new CreatedAccount($key, $address);
    }

    protected function handleRegisterCurrency(RegisterCurrency $c) {
        if (!trim($c->getName())) {
            throw new \Exception('The name cannot be empty.');
        }

        if (in_array($c->getName(), $this->registeredCurrencies)) {
            throw new \Exception('A currency is already registered under this name.');
        }

        $currency = CurrencyIdentifier::fromBinary($this->auth->getAddress($c->getCurrency()));
        $this->record(new CurrencyRegistered($currency, $c->getName()));
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->registeredCurrencies[] = $e->getName();
    }

    protected function handleCreateBacker(CreateBacker $c) {
        $key = $this->lib->generateKey();
        $backer = BackerIdentifier::fromBinary($this->lib->getAddress($key));
        $this->record(new BackerCreated($backer, $key));

        if ($c->getName()) {
            if (in_array($c->getName(), $this->registeredBackers)) {
                throw new \Exception('A backer with this name is already registered.');
            }
            $this->record(new BackerRegistered($backer, $c->getName()));
        }

        if ($c->getDetails()) {
            $this->record(new BackerDetailsChanged($backer, $c->getDetails()));
        }
    }

    protected function applyBackerRegistered(BackerRegistered $e) {
        $this->registeredBackers[] = $e->getName();
    }
}