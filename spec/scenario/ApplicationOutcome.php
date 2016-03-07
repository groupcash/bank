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
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CreatedAccount;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Authorization;
use groupcash\php\model\RuleBook;
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

    private function enc($data) {
        return base64_encode($data);
    }

    private function shouldHaveRecorded(DomainEvent $event) {
        $this->assert->contains($this->events->readAll()->getEvents(), $event);
    }

    private function shouldNotHaveRecorded($class) {
        $this->assert->not(array_filter($this->events->readAll()->getEvents(),
            function (DomainEvent $event) use ($class) {
                return is_a($event, $class);
            }));
    }

    public function ItShouldReturnANewAccountWithTheKey_AndTheAddress($key, $address) {
        $this->assert->equals($this->return->value,
            new CreatedAccount(new Binary($key), new Binary($address)));
    }

    public function AnAccount_ShouldBeCreated($account) {
        $this->shouldHaveRecorded(new AccountCreated(new AccountIdentifier(base64_encode($account))));
    }

    public function ItShouldFailWith($message) {
        $this->except->thenTheException_ShouldBeThrown($message);
    }

    public function ACurrency_WithTheRules_ShouldBeEstablished($currency, $rules) {
        $this->shouldHaveRecorded(
            new CurrencyEstablished(
                new CurrencyIdentifier($this->enc($currency)),
                new RuleBook(
                    new Binary($currency),
                    $rules,
                    null,
                    "$currency\0$rules\0 signed with $currency key"
                )));
    }

    public function TheCurrency_ShouldBeRegisteredAs($currency, $name) {
        $this->shouldHaveRecorded(new CurrencyRegistered(
            new CurrencyIdentifier($this->enc($currency)),
            $name
        ));
    }

    public function NoCurrencyShouldBeRegistered() {
        $this->shouldNotHaveRecorded(CurrencyRegistered::class);
    }

    public function ANewBacker_ShouldBeCreated($backer) {
        $this->shouldHaveRecorded(new BackerCreated(
            new BackerIdentifier(base64_encode($backer)),
            new Binary("$backer key")
        ));
    }

    public function TheBacker_ShouldBeRegisteredUnder($backer, $name) {
        $this->shouldHaveRecorded(new BackerRegistered(
            new BackerIdentifier(base64_encode($backer)),
            $name
        ));
    }

    public function TheDetailsOfBacker_ShouldBeChangedTo($backer, $details) {
        $this->shouldHaveRecorded(new BackerDetailsChanged(
            new BackerIdentifier(base64_encode($backer)),
            $details
        ));
    }

    public function TheIssuer_ShouldBeAuthorizedBy($issuer, $currency) {
        $this->shouldHaveRecorded(new IssuerAuthorized(
            new CurrencyIdentifier(base64_encode($currency)),
            new AccountIdentifier(base64_encode($issuer)),
            new Authorization(
                new Binary($issuer),
                new Binary($currency),
                "$issuer signed with $currency key"
            )
        ));
    }
}