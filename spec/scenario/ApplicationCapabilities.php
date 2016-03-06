<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\Application;
use groupcash\bank\app\crypto\FakeCryptography;
use groupcash\bank\app\sourced\store\EventStore;
use groupcash\bank\CreateAccount;
use groupcash\bank\EstablishCurrency;
use groupcash\bank\model\Authentication;
use groupcash\php\algorithms\FakeAlgorithm;
use groupcash\php\Groupcash;
use groupcash\php\model\signing\Binary;

class ApplicationCapabilities {

    /** @var ReturnValue */
    private $return;

    /** @var EventStore */
    private $events;

    /** @var Application */
    private $app;

    /**
     * @param ReturnValue $return
     * @param EventStore $events
     */
    public function __construct(ReturnValue $return, EventStore $events) {
        $this->return = $return;
        $this->events = $events;

        $this->app = new Application($events, new Groupcash(new FakeAlgorithm()), new FakeCryptography());
    }

    public function handle($command) {
        $this->return->value = $this->app->handle($command);
    }

    public function ICreateAnAccount() {
        $this->handle(new CreateAccount());
    }

    public function ICreateAnAccountWithThePassword($password) {
        $this->handle(new CreateAccount($password));
    }

    public function _EstablishesACurrencyWithTheRules($key, $rules) {
        $this->handle(new EstablishCurrency(new Authentication(new Binary($key)), $rules));
    }

    public function _EstablishesACurrencyWithTheRules_UnderTheName($key, $rules, $name) {
        $this->handle(new EstablishCurrency(new Authentication(new Binary($key)), $rules, $name));
    }
}