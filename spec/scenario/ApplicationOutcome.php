<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\events\AccountCreated;
use groupcash\bank\app\sourced\store\EventStore;
use groupcash\bank\model\CreatedAccount;
use groupcash\php\model\signing\Binary;
use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;

class ApplicationOutcome {

    /** @var Assert */
    private $assert;

    /** @var ExceptionFixture */
    private $except;

    /** @var ReturnValue */
    private $return;

    /** @var EventStore */
    private $events;

    /**
     * @param Assert $assert
     * @param ExceptionFixture $except
     * @param ReturnValue $return
     * @param EventStore $events
     */
    public function __construct(Assert $assert, ExceptionFixture $except, ReturnValue $return, EventStore $events) {
        $this->assert = $assert;
        $this->except = $except;
        $this->return = $return;
        $this->events = $events;
    }

    public function ItShouldReturnANewAccountWithTheKey_AndTheAddress($key, $address) {
        $this->assert->equals($this->return->value, new CreatedAccount(new Binary($key), new Binary($address)));
    }

    public function AnAccountWithTheAddress_ShouldBeCreated($address) {
        $this->assert->equals($this->events->readAll()->getEvents(), [
            new AccountCreated(new Binary($address))
        ]);
    }
}