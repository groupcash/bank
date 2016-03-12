<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\EventStore;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\BackerDetailsChanged;
use groupcash\bank\events\BackerRegistered;
use groupcash\bank\events\CoinIssued;
use groupcash\bank\events\CoinReceived;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\projecting\GeneratedAccount;
use groupcash\php\algorithms\FakeAlgorithm;
use groupcash\php\Groupcash;
use groupcash\php\model\Authorization;
use groupcash\php\model\Base;
use groupcash\php\model\Coin;
use groupcash\php\model\Input;
use groupcash\php\model\Output;
use groupcash\php\model\RuleBook;
use groupcash\php\model\signing\Binary;
use groupcash\php\model\value\Fraction;
use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\reflect\ValuePrinter;

class ApplicationOutcome {

    /** @var Assert */
    private $assert;

    /** @var ExceptionFixture */
    private $except;

    /** @var ReturnValue */
    private $return;

    /** @var EventStore */
    private $events;

    /** @var Groupcash */
    private $lib;

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

        $this->lib = new Groupcash(new FakeAlgorithm());
    }

    private function enc($data) {
        return base64_encode($data);
    }

    private function shouldHaveRecordedEvent($event) {
        $this->assert->contains($this->events->allEvents(), $event);
    }

    private function shouldHaveRecorded(callable $filter) {
        $domainEvents = $this->events->allEvents();

        $this->assert->not()->size(array_filter($domainEvents, $filter), 0,
            'Not found in ' . ValuePrinter::serialize($domainEvents));
    }

    private function shouldNotHaveRecorded($class) {
        $this->assert->not(array_filter($this->events->allEvents(),
            function ($event) use ($class) {
                return is_a($event, $class);
            }));
    }

    public function ItShouldReturnANewAccountWithTheKey_AndTheAddress($key, $address) {
        $account = $this->return->value;
        if (!($account instanceof GeneratedAccount)) {
            $this->assert->isInstanceOf($account, GeneratedAccount::class);
        }
        $this->assert->equals($account->getKey(), new Binary($key));
        $this->assert->equals($account->getAddress(), new Binary($address));
    }

    public function ItShouldFailWith($message) {
        $this->except->thenTheException_ShouldBeThrown($message);
    }

    public function ACurrency_WithTheRules_ShouldBeEstablished($currency, $rules) {
        $this->shouldHaveRecordedEvent(
            new CurrencyEstablished(
                new CurrencyIdentifier($this->enc($currency)),
                new RuleBook(
                    new Binary($currency),
                    $rules,
                    "$currency\0$rules\0 signed with $currency key"
                )));
    }

    public function TheCurrency_ShouldBeRegisteredAs($currency, $name) {
        $this->shouldHaveRecordedEvent(new CurrencyRegistered(
            new CurrencyIdentifier($this->enc($currency)),
            $name
        ));
    }

    public function NoCurrencyShouldBeRegistered() {
        $this->shouldNotHaveRecorded(CurrencyRegistered::class);
    }

    public function ANewBacker_ShouldBeCreatedFor_By($backer, $currency, $issuer) {
        $this->shouldHaveRecordedEvent(new BackerCreated(
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($issuer)),
            new BackerIdentifier($this->enc($backer)),
            new Binary("$backer key")
        ));
    }

    public function TheBacker_ShouldBeRegisteredUnder($backer, $name) {
        $this->shouldHaveRecordedEvent(new BackerRegistered(
            new BackerIdentifier($this->enc($backer)),
            $name
        ));
    }

    public function TheDetailsOfBacker_ShouldBeChangedTo($backer, $details) {
        $this->shouldHaveRecordedEvent(new BackerDetailsChanged(
            new BackerIdentifier($this->enc($backer)),
            $details
        ));
    }

    public function TheIssuer_ShouldBeAuthorizedBy($issuer, $currency) {
        $this->shouldHaveRecordedEvent(new IssuerAuthorized(
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($issuer)),
            new Authorization(
                new Binary($issuer),
                new Binary($currency),
                "$issuer signed with $currency key"
            )
        ));
    }

    public function ACoinWorth__BackedBy_ShouldBeIssuedTo_SignedBy($value, $currency, $description, $backer, $issuer) {
        $this->shouldHaveRecordedEvent(new CoinIssued(
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($issuer)),
            new BackerIdentifier($this->enc($backer)),
            new Coin(new Input(new Base(
                new Binary($currency),
                $description,
                new Output(
                    new Binary($backer),
                    new Fraction($value)
                ),
                new Binary($issuer),
                "$currency\0$description\0$backer\0$value|1 signed with $issuer key"
            ), 0))
        ));
    }

    public function _ShouldReceiveACoinWorth($account, $value, $currency) {
        $this->shouldHaveRecorded(function ($event) use ($account, $value, $currency) {
            return
                $event instanceof CoinReceived
                && $event->getTarget()->getIdentifier() == $this->enc($account)
                && $event->getCurrency() == new CurrencyIdentifier($this->enc($currency))
                && $event->getCoin()->getOwner() == new Binary($account)
                && $event->getCoin()->getValue() == new Fraction($value)
                && $event->getCoin()->getCurrency() == new Binary($currency);
        });
    }

    public function Coin_Worth_ShouldBeSentFrom_To($description, $value, $currency, $owner, $target) {
        $this->shouldHaveRecorded(function ($event) use ($description, $owner, $value, $currency, $target) {
            return
                $event instanceof CoinsSent
                && in_array($this->coin($owner, $value, $currency, $description), $event->getCoins())
                && $event->getTransferred()->getOwner() == new Binary($target);
        });
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