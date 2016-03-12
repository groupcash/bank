<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\EventStore;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\BackerRegistered;
use groupcash\bank\events\CoinIssued;
use groupcash\bank\events\CoinReceived;
use groupcash\bank\events\CoinsRequested;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\events\RequestApproved;
use groupcash\bank\events\RequestCancelled;
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
        $this->events->append(new CurrencyEstablished(
            new CurrencyIdentifier($this->enc($currency)),
            new RuleBook(
                new Binary($currency),
                'whatever',
                'signed by me'
            )), new CurrencyIdentifier($this->enc($currency)));
    }

    public function ACurrencyWasRegisteredUnder($name) {
        $this->events->append(new CurrencyRegistered(
            new CurrencyIdentifier('foo'),
            $name
        ), BankIdentifier::singleton());
    }

    public function ABackerWasRegisteredUnder($name) {
        $this->events->append(new BackerRegistered(
            new BackerIdentifier('foo'),
            $name
        ), BankIdentifier::singleton());
    }

    public function _HasAuthorized($currency, $issuer) {
        $this->events->append(new IssuerAuthorized(
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier(base64_encode($issuer)),
            new Authorization(
                new Binary($issuer),
                new Binary($currency),
                'some signature'
            )
        ), new CurrencyIdentifier($this->enc($currency)));
    }

    public function _HasReceivedACoin_Worth($account, $description, $value, $currency) {
        $this->events->append(new CoinReceived(
            new AccountIdentifier($this->enc($account)),
            new CurrencyIdentifier($this->enc($currency)),
            $this->coin($account, $value, $currency, $description)
        ), new AccountIdentifier($this->enc($account)));
    }

    public function _HasSentACoin_Worth__To($owner, $description, $value, $currency, $target) {
        $this->events->append(new CoinsSent(
            new AccountIdentifier($this->enc($owner)),
            new AccountIdentifier($this->enc($target)),
            new CurrencyIdentifier($this->enc($currency)),
            [$this->coin($owner, $value, $currency, $description)],
            $this->coin($owner, $value, $currency, 'Transferred')
        ), new AccountIdentifier($this->enc($owner)));
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

    public function _HasIssued__To($issuer, $value, $currency, $backer) {
        $this->events->append(new CoinIssued(
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($issuer)),
            new BackerIdentifier($this->enc($backer)),
            $this->coin($backer, $value, $currency, 'A Coin')
        ), new CurrencyIdentifier($this->enc($currency)));
    }

    public function ABacker_WasCreatedFor($backer, $currency) {
        $this->events->append(new BackerCreated(
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier('issuer'),
            new BackerIdentifier($this->enc($backer)),
            new Binary("$backer key")
        ), new CurrencyIdentifier($this->enc($currency)));
    }

    public function _HasRequested($account, $value, $currency) {
        $this->events->append(new CoinsRequested(
            new AccountIdentifier($this->enc($account)),
            new CurrencyIdentifier($this->enc($currency)),
            new Fraction($value)
        ), new CurrencyIdentifier($this->enc($currency)));
    }

    public function TheRequestBy_For_WasCancelled($account, $currency) {
        $this->events->append(new RequestCancelled(
            new AccountIdentifier($this->enc('some issuer')),
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($account))
        ), new CurrencyIdentifier($this->enc($currency)));
    }

    public function TheRequestBy_For_WasApprovedWithTheContributions($account, $currency, $contributions) {
        $event = new RequestApproved(
            new AccountIdentifier($this->enc('some issuer')),
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($account))
        );

        foreach ($contributions as $backer => $value) {
            $event->addContribution(
                new BackerIdentifier($this->enc($backer)),
                new Fraction($value)
            );
        }

        $this->events->append($event, new CurrencyIdentifier($this->enc($currency)));
    }
}