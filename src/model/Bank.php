<?php
namespace groupcash\bank\model;

use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\CreateAccount;
use groupcash\bank\CreateBacker;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\RegisterCurrency;
use groupcash\php\Groupcash;

class Bank extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Authenticator */
    private $auth;

    /** @var CurrencyIdentifier[] */
    private $currencies = [];

    /** @var string[] */
    private $backerNames = [];

    /**
     * @param Groupcash $lib
     * @param Authenticator $auth
     */
    public function __construct(Groupcash $lib, Authenticator $auth) {
        $this->lib = $lib;
        $this->auth = $auth;
    }

    public function handleCreateAccount(CreateAccount $c) {
        $key = $this->lib->generateKey();

        return new CreatedAccount($this->lib->getAddress($key), $this->auth->encrypt($key, $c->getPassword()));
    }

    public function handleRegisterCurrency(RegisterCurrency $c) {
        if (array_key_exists($c->getName(), $this->currencies)) {
            throw new \Exception('A currency with this name is already registered.');
        }

        $currency = new CurrencyIdentifier((string)$c->getCurrency());

        if (in_array($currency, $this->currencies)) {
            throw new \Exception('This currency is already registered.');
        }

        $this->record(new CurrencyRegistered(
            $currency,
            $c->getName()));
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->currencies[$e->getName()] = $e->getCurrency();
    }

    public function handleCreateBacker(CreateBacker $c) {
        $key = $this->lib->generateKey();
        $address = $this->lib->getAddress($key);

        if (in_array($c->getName(), $this->backerNames)) {
            throw new \Exception('A backer with this name already exists.');
        }

        $this->record(new BackerCreated(
            new BackerIdentifier($address),
            $key,
            $c->getName()
        ));

        return new CreatedAccount($address);
    }

    protected function applyBackerCreated(BackerCreated $e) {
        $this->backerNames[] = $e->getName();
    }
}