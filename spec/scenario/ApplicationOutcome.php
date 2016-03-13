<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\Specification;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\BackerDetailsChanged;
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

class ApplicationOutcome {

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

    private function shouldHaveRecordedEvent($event) {
        $this->specification->thenShould($event);
    }

    private function shouldHaveRecorded($eventClass, callable $filter) {
        $this->specification->thenShould($eventClass, $filter);
    }

    private function shouldNotHaveRecorded($class) {
        $this->specification->thenShouldNot($class);
    }

    public function ItShouldReturnANewAccountWithTheKey_AndTheAddress($key, $address) {
        $this->specification->thenItShouldReturn(function ($returned) use ($key, $address) {
            return
                $returned instanceof GeneratedAccount
                && $returned->getKey() == new Binary($key)
                && $returned->getAddress() == new Binary($address);
        });
    }

    public function ItShouldFailWith($message) {
        $this->specification->thenItShouldFailWith($message);
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
        $this->shouldHaveRecorded(CoinReceived::class, function (CoinReceived $event) use ($account, $value, $currency) {
            return
                $event->getTarget()->getIdentifier() == $this->enc($account)
                && $event->getCurrency() == new CurrencyIdentifier($this->enc($currency))
                && $event->getCoin()->getOwner() == new Binary($account)
                && $event->getCoin()->getValue() == new Fraction($value)
                && $event->getCoin()->getCurrency() == new Binary($currency);
        });
    }

    public function Coin_Worth_ShouldBeSentFrom_To($description, $value, $currency, $owner, $target) {
        $this->shouldHaveRecorded(CoinsSent::class, function (CoinsSent $event) use ($description, $owner, $value, $currency, $target) {
            return
                in_array($this->coin($owner, $value, $currency, $description), $event->getCoins())
                && $event->getTransferred()->getOwner() == new Binary($target);
        });
    }

    public function ACoinWorth_ShouldBeSentFrom_To($value, $currency, $owner, $target) {
        $this->shouldHaveRecorded(CoinsSent::class, function (CoinsSent $event) use ($owner, $value, $currency, $target) {
            return
                $event->getCoins()[0]->getOwner() == new Binary($owner)
                && $event->getTransferred()->getCurrency() == new Binary($currency)
                && $event->getTransferred()->getValue() == new Fraction($value)
                && $event->getTransferred()->getOwner() == new Binary($target);
        });
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

    public function ThereShouldBe__RequestedBy($value, $currency, $account) {
        $this->shouldHaveRecordedEvent(new CoinsRequested(
            new AccountIdentifier($this->enc($account)),
            new CurrencyIdentifier($this->enc($currency)),
            new Fraction($value)
        ));
    }

    public function theRequestFrom_For_ShouldBeCancelled_By($account, $currency, $issuer) {
        $this->shouldHaveRecordedEvent(new RequestCancelled(
            new AccountIdentifier($this->enc($issuer)),
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($account))
        ));
    }

    public function TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions($account, $currency, $issuer, $contributions) {
        $event = new RequestApproved(
            new AccountIdentifier($this->enc($issuer)),
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($account))
        );
        foreach ($contributions as $backer => $contribution) {
            $event->addContribution(new BackerIdentifier($this->enc($backer)), new Fraction($contribution));
        }
        $this->shouldHaveRecordedEvent($event);
    }
}