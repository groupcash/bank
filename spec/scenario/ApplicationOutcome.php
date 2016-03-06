<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\events\AccountCreated;
use groupcash\bank\app\sourced\store\EventStore;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\BackerDetailsChanged;
use groupcash\bank\events\BackerRegistered;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\model\CreatedAccount;
use groupcash\php\model\Authorization;
use groupcash\php\model\CurrencyRules;
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
        $this->shouldHaveRecorded(new AccountCreated(new Binary($address)));
    }

    private function shouldHaveRecorded(DomainEvent $event) {
        $this->assert->contains($this->events->readAll()->getEvents(), $event);
    }

    public function ItShouldFailWith($message) {
        $this->except->thenTheException_ShouldBeThrown($message);
    }

    public function ACurrency_WithTheRules_SignedBy_ShouldBeEstablished($address, $rules, $key) {
        $this->shouldHaveRecorded(
            new CurrencyEstablished(new CurrencyRules(
                new Binary($address),
                $rules,
                null,
                "$address\0$rules\0 signed with $key"
            )));
    }

    public function TheCurrency_ShouldBeRegisteredAs($address, $name) {
        $this->shouldHaveRecorded(new CurrencyRegistered(
            new Binary($address),
            $name
        ));
    }

    public function NoCurrencyShouldBeRegistered() {
        $this->shouldNotHaveRecorded(CurrencyRegistered::class);
    }

    private function shouldNotHaveRecorded($class) {
        $this->assert->not(array_filter($this->events->readAll()->getEvents(),
            function (DomainEvent $event) use ($class) {
                return is_a($event, $class);
            }));
    }

    public function ANewBackerWithTheKey_ShouldBeCreated($key) {
        $this->shouldHaveRecorded(new BackerCreated(
            new Binary($key)
        ));
    }

    public function TheBacker_ShouldBeRegisteredUnder($address, $name) {
        $this->shouldHaveRecorded(new BackerRegistered(
            new Binary($address),
            $name
        ));
    }

    public function TheDetailsOfBacker_ShouldBeChangedTo($address, $details) {
        $this->shouldHaveRecorded(new BackerDetailsChanged(
            new Binary($address),
            $details
        ));
    }

    public function TheIssuer_ShouldBeAuthorizedBy($issuer, $currency) {
        $this->shouldHaveRecorded(new IssuerAuthorized(
            new Authorization(
                new Binary($issuer),
                new Binary($currency),
                "$issuer signed with $currency key"
            )
        ));
    }
}