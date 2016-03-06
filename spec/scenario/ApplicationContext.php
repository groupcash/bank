<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\store\EventStore;
use groupcash\bank\events\BackerRegistered;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Authorization;
use groupcash\php\model\CurrencyRules;
use groupcash\php\model\signing\Binary;

class ApplicationContext {

    /** @var EventStore */
    private $events;

    /**
     * @param EventStore $events
     */
    public function __construct(EventStore $events) {
        $this->events = $events;
    }

    public function TheCurrency_WasEstablished($address) {
        $this->events->add(new CurrencyIdentifier((string)new Binary($address)),
            new CurrencyEstablished(new CurrencyRules(
                new Binary($address),
                'whatever',
                null,
                'signed by me'
            )));
    }

    public function ACurrencyWasRegisteredUnder($name) {
        $this->events->add(BankIdentifier::singleton(),
            new CurrencyRegistered(
                new Binary('foo'),
                $name
            ));
    }

    public function ABackerWasRegisteredUnder($name) {
        $this->events->add(BankIdentifier::singleton(),
            new BackerRegistered(
                new Binary('foo'),
                $name
            ));
    }

    public function _HasAuthorized($currency, $issuer) {
        $this->events->add(new CurrencyIdentifier((string)new Binary($currency)),
            new IssuerAuthorized(
                new Authorization(
                    new Binary($issuer),
                    new Binary($currency),
                    'some signature'
                )
            ));
    }
}