<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\store\EventStore;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\model\BankIdentifier;
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
        $this->events->add(BankIdentifier::singleton(),
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
}