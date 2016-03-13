<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\Specification;
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
use groupcash\bank\model\Time;
use groupcash\php\algorithms\FakeAlgorithm;
use groupcash\php\Groupcash;
use groupcash\php\model\Authorization;
use groupcash\php\model\Output;
use groupcash\php\model\RuleBook;
use groupcash\php\model\signing\Binary;
use groupcash\php\model\value\Fraction;

class SpecificationContext {

    /** @var Specification */
    private $specification;

    /**
     * @param Specification $specification
     */
    public function __construct(Specification $specification) {
        $this->specification = $specification;
    }

    private function enc($data) {
        return base64_encode($data);
    }

    public function TheCurrency_WasEstablished($currency) {
        $this->specification->given(new CurrencyEstablished(
            new CurrencyIdentifier($this->enc($currency)),
            new RuleBook(
                new Binary($currency),
                'whatever',
                'signed by me'
            )), new CurrencyIdentifier($this->enc($currency)));
    }

    public function ACurrencyWasRegisteredUnder($name) {
        $this->specification->given(new CurrencyRegistered(
            new CurrencyIdentifier('foo'),
            $name
        ), BankIdentifier::singleton());
    }

    public function ABackerWasRegisteredUnder($name) {
        $this->specification->given(new BackerRegistered(
            new BackerIdentifier('foo'),
            $name
        ), BankIdentifier::singleton());
    }

    public function _HasAuthorized($currency, $issuer) {
        $this->specification->given(new IssuerAuthorized(
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
        $this->_HasReceivedACoin_WorthWithTheSubject($account, $description, $value, $currency, null);
    }

    public function _HasReceivedACoin_WorthWithTheSubject($account, $description, $value, $currency, $subject) {
        $this->specification->given(new CoinReceived(
            new AccountIdentifier($this->enc($account)),
            new CurrencyIdentifier($this->enc($currency)),
            $this->coin($account, $value, $currency, $description),
            $subject
        ), new AccountIdentifier($this->enc($account)));
    }

    public function _HasSentACoin_Worth__To($owner, $description, $value, $currency, $target) {
        $this->_HasSentACoin_Worth__To_WithSubject($owner, $description, $value, $currency, $target, null);
    }

    public function _HasSentACoin_Worth__To_WithSubject($owner, $description, $value, $currency, $target, $subject) {
        $this->specification->given(new CoinsSent(
            new AccountIdentifier($this->enc($owner)),
            new AccountIdentifier($this->enc($target)),
            new CurrencyIdentifier($this->enc($currency)),
            [$this->coin($owner, $value, $currency, $description)],
            $this->coin($owner, $value, $currency, 'Transferred'),
            $subject
        ), new AccountIdentifier($this->enc($owner)));
    }

    private function coin($owner, $value, $currency, $description) {
        $lib = new Groupcash(new FakeAlgorithm());
        return $lib->issueCoin(
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
        $this->specification->given(new CoinIssued(
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($issuer)),
            new BackerIdentifier($this->enc($backer)),
            $this->coin($backer, $value, $currency, 'A Coin')
        ), new CurrencyIdentifier($this->enc($currency)));
    }

    public function ABacker_WasCreatedFor($backer, $currency) {
        $this->specification->given(new BackerCreated(
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier('issuer'),
            new BackerIdentifier($this->enc($backer)),
            new Binary("$backer key")
        ), new CurrencyIdentifier($this->enc($currency)));
    }

    public function _HasRequested($account, $value, $currency) {
        $this->specification->given(new CoinsRequested(
            new AccountIdentifier($this->enc($account)),
            new CurrencyIdentifier($this->enc($currency)),
            new Fraction($value)
        ), new CurrencyIdentifier($this->enc($currency)));
    }

    public function TheRequestBy_For_WasCancelled($account, $currency) {
        $this->specification->given(new RequestCancelled(
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

        $this->specification->given($event, new CurrencyIdentifier($this->enc($currency)));
    }

    public function NowIs($when) {
        Time::$frozen = new \DateTimeImmutable($when);
    }
}