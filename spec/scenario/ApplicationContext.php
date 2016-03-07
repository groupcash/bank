<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\store\EventStore;
use groupcash\bank\events\BackerRegistered;
use groupcash\bank\events\CoinReceived;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\algorithms\FakeAlgorithm;
use groupcash\php\Groupcash;
use groupcash\php\model\Authorization;
use groupcash\php\model\Output;
use groupcash\php\model\RuleBook;
use groupcash\php\model\signing\Binary;
use groupcash\php\model\value\Fraction;

class ApplicationContext {

    /** @var EventStore */
    private $events;

    /** @var Groupcash */
    private $lib;

    /**
     * @param EventStore $events
     */
    public function __construct(EventStore $events) {
        $this->events = $events;
        $this->lib = new Groupcash(new FakeAlgorithm());
    }

    private function enc($data) {
        return base64_encode($data);
    }

    public function TheCurrency_WasEstablished($currency) {
        $this->events->add(new CurrencyIdentifier($this->enc($currency)),
            new CurrencyEstablished(
                new CurrencyIdentifier($this->enc($currency)),
                new RuleBook(
                    new Binary($currency),
                    'whatever',
                    null,
                    'signed by me'
                )));
    }

    public function ACurrencyWasRegisteredUnder($name) {
        $this->events->add(BankIdentifier::singleton(),
            new CurrencyRegistered(
                new CurrencyIdentifier('foo'),
                $name
            ));
    }

    public function ABackerWasRegisteredUnder($name) {
        $this->events->add(BankIdentifier::singleton(),
            new BackerRegistered(
                new BackerIdentifier('foo'),
                $name
            ));
    }

    public function _HasAuthorized($currency, $issuer) {
        $this->events->add(new CurrencyIdentifier($this->enc($currency)),
            new IssuerAuthorized(
                new CurrencyIdentifier($this->enc($currency)),
                new AccountIdentifier(base64_encode($issuer)),
                new Authorization(
                    new Binary($issuer),
                    new Binary($currency),
                    'some signature'
                )
            ));
    }

    public function _HasReceived($account, $value, $currency) {
        $this->events->add(new AccountIdentifier($this->enc($account)),
            new CoinReceived(
                new AccountIdentifier($this->enc($account)),
                new CurrencyIdentifier($this->enc($currency)),
                $this->coin($account, $value, $currency, 'Foo')
            ));
    }

    public function _HasSent__To($owner, $value, $currency, $target) {
        $this->events->add(new AccountIdentifier($this->enc($owner)),
            new CoinsSent(
                new AccountIdentifier($this->enc($owner)),
                new AccountIdentifier($this->enc($target)),
                new CurrencyIdentifier($this->enc($currency)),
                [$this->coin($owner, $value, $currency, 'Foo')],
                $this->coin($owner, $value, $currency, 'Transferred')
        ));
    }

    private function coin($owner, $value, $currency, $description) {
        return $this->lib->issueCoin(
            new Binary('foo key'),
            new Binary($currency),
            $description,
            new Output(
                new Binary($owner),
                new Fraction($value)
            )
        );
    }
}