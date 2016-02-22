<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\CreateBacker;
use groupcash\bank\AddBacker;
use groupcash\bank\app\Application;
use groupcash\bank\app\sourced\domain\Time;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CreateAccount;
use groupcash\bank\DeclarePromise;
use groupcash\bank\DepositCoins;
use groupcash\bank\IssueCoins;
use groupcash\bank\ListBackers;
use groupcash\bank\ListCurrencies;
use groupcash\bank\ListTransactions;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\projecting\AllBackers;
use groupcash\bank\projecting\AllCurrencies;
use groupcash\bank\projecting\Currency;
use groupcash\bank\projecting\Transaction;
use groupcash\bank\projecting\TransactionHistory;
use groupcash\bank\RegisterCurrency;
use groupcash\bank\SendCoins;
use groupcash\bank\WithdrawCoins;
use groupcash\php\Groupcash;
use groupcash\php\model\Coin;
use groupcash\php\model\Fraction;
use groupcash\php\model\Promise;
use groupcash\php\model\Signer;
use groupcash\php\model\Transference;
use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use spec\groupcash\bank\fakes\FakeCryptography;
use spec\groupcash\bank\fakes\FakeEventStore;
use spec\groupcash\bank\fakes\FakeKeyService;
use spec\groupcash\bank\fakes\FakeRandomNumberGenerator;
use spec\groupcash\bank\fakes\FakeVault;

class ApplicationFixture {

    /** @var FakeEventStore */
    private $store;

    /** @var FakeKeyService */
    private $key;

    /** @var Application */
    private $app;

    /** @var ExceptionFixture */
    private $except;

    /** @var Assert */
    private $assert;

    /** @var mixed */
    private $returned;

    /** @var TransactionHistory */
    private $transactionHistory;

    /** @var AllCurrencies */
    private $currencies;

    /** @var AllBackers */
    private $backers;

    /** @var Coin[] */
    private $coins;

    /** @var Coin[] */
    private $thoseCoins = [];

    public function __construct(Assert $assert, ExceptionFixture $except) {
        $this->assert = $assert;
        $this->except = $except;

        $this->key = new FakeKeyService();
        $this->store = new FakeEventStore();
        $this->app = new Application(
            $this->store,
            new FakeCryptography(),
            new Groupcash(
                $this->key
            ),
            new FakeVault(
                new FakeRandomNumberGenerator('secret')
            )
        );
    }

    public function _Sends__To($owner, $amount, $currency, $target) {
        $this->_Sends__To_WithSubject($owner, $amount, $currency, $target, null);
    }

    public function _Sends__To_WithSubject($owner, $amount, $currency, $target, $subject) {
        $this->app->handle(new SendCoins(
            new Authentication("private $owner"),
            $this->toFraction($amount),
            new CurrencyIdentifier($currency),
            new AccountIdentifier($target),
            $subject
        ));
    }

    public function itShouldFailWith($message) {
        $this->except->thenTheException_ShouldBeThrown($message);
    }

    public function _issues__to($issuer, $number, $currency, $backer) {
        $this->app->handle(new IssueCoins(
            new Authentication("private $issuer"),
            $number,
            new CurrencyIdentifier($currency),
            new BackerIdentifier($backer)
        ));
    }

    public function _issuesAll_to($issuer, $currency, $backer) {
        $this->_issues__to($issuer, null, $currency, $backer);
    }

    public function _Authorizes($currency, $issuer) {
        $this->app->handle(new AuthorizeIssuer(
            new Authentication("private $currency"),
            new AccountIdentifier($issuer)
        ));
    }

    public function ICreateABacker($backer) {
        $this->ICreateABacker_Named($backer, "name of $backer");
    }

    public function ICreateABacker_Named($backer, $name) {
        $this->key->nextKey = $backer;

        $this->app->handle(new CreateBacker(
            $name
        ));
    }

    public function _Adds_To($issuer, $backer, $currency) {
        $this->app->handle(new AddBacker(
            new Authentication("private $issuer"),
            new CurrencyIdentifier($currency),
            new BackerIdentifier($backer)
        ));
    }

    public function _Declares_Of_By_For($issuer, $limit, $promise, $backer, $currency) {
        $this->app->handle(new DeclarePromise(
            new Authentication("private $issuer"),
            new BackerIdentifier($backer),
            new CurrencyIdentifier($currency),
            $promise,
            $limit
        ));
    }

    public function allShouldBeFine() {
        $this->except->thenNoExceptionShouldBeThrown();
    }

    public function ICreateAnAccountWithPassword($password) {
        $this->returned = $this->app->handle(new CreateAccount($password));
    }

    public function itShouldReturn($value) {
        $this->assert->equals($this->returned, $value);
    }

    public function _ListsTheirTransactions($account) {
        $this->transactionHistory = $this->app->handle(new ListTransactions(
            new Authentication("private $account")
        ));
    }

    public function thereShouldBe_Transactions($int) {
        $this->assert->size($this->transactionHistory->getTransactions(), $int);
    }

    public function nowIs($when) {
        Time::$frozen = new \DateTimeImmutable($when);
    }

    public function transaction_ShouldBeOf__On($pos, $amount, $currency, $when) {
        $this->assert->equals($this->transactionHistory->getTransactions()[$pos - 1],
            new Transaction(
                new \DateTimeImmutable($when),
                new Currency(new CurrencyIdentifier($currency)),
                $this->toFraction($amount)
            ));
    }

    public function transaction_ShouldHaveTheSubject($pos, $subject) {
        $this->assert->equals($this->transactionHistory->getTransactions()[$pos - 1]->getSubject(), $subject);
    }

    public function transaction_ShouldHaveTheCurrencyName($pos, $name) {
        $this->assert->equals($this->transactionHistory->getTransactions()[$pos - 1]->getCurrency()->getName(), $name);
    }

    public function thereShouldBeATotalOf($amount, $currency) {
        $this->assert->equals($this->transactionHistory->getTotal(new CurrencyIdentifier($currency)), $this->toFraction($amount));
    }

    private function toFraction($amount) {
        $factor = 1;
        do {
            $factor++;
        } while ($amount * $factor != (int)($amount * $factor));

        return new Fraction($amount * $factor, $factor);
    }

    public function IRegister_As($address, $currency) {
        $this->app->handle(new RegisterCurrency(
            new AccountIdentifier($address),
            $currency
        ));
    }

    public function IListAllCurrencies() {
        $this->currencies = $this->app->handle(new ListCurrencies());
    }

    public function thereShouldBe_Currencies($int) {
        $this->assert->size($this->currencies->getCurrencies(), $int);
    }

    public function currency_ShouldHaveTheAddress_AndTheName($pos, $address, $name) {
        $currency = $this->currencies->getCurrencies()[$pos - 1];
        $this->assert->equals($currency->getAddress(), new CurrencyIdentifier($address));
        $this->assert->equals($currency->getName(), $name);
    }

    public function IListAllBackers() {
        $this->backers = $this->app->handle(new ListBackers());
    }

    public function thereShouldBe_Backers($int) {
        $this->assert->size($this->backers->getBackers(), $int);
    }

    public function backer_shouldHaveTheName($pos, $name) {
        $this->assert->equals($this->backers->getBackers()[$pos - 1]->getName(), $name);
    }

    public function backer_shouldHaveTheAddress($pos, $address) {
        $this->assert->equals($this->backers->getBackers()[$pos - 1]->getAddress(), new BackerIdentifier($address));
    }

    public function backer_shouldHaveTheCurrencies($pos, $currencies) {
        $currencyIdentifiers = array_map(function (Currency $currency) {
            return (string)$currency->getAddress();
        }, $this->backers->getBackers()[$pos - 1]->getCurrencies());
        $this->assert->equals($currencyIdentifiers, $currencies);
    }

    public function _Withdraws($account, $amount, $currency) {
        return $this->coins = $this->app->handle(new WithdrawCoins(
            new Authentication("private $account"),
            new CurrencyIdentifier($currency),
            is_null($amount) ? null : $this->toFraction($amount)
        ));
    }

    public function thereShouldBe_Coins($int) {
        $this->assert->size($this->coins, $int);
    }

    public function coin_ShouldBe__Promising__By_TransferredTo($pos, $amount, $currency, $promise, $serial, $backer, $target) {
        $coin = $this->coins[$pos - 1];

        $transference = $coin->getTransaction();
        if (!($transference instanceof Transference)) {
            $this->assert->fail("Not a transference");
        }

        $this->assert->equals($transference->getCoin()->getTransaction(), new Promise(
            $currency,
            $backer,
            $promise,
            $serial
        ));

        $this->assert->equals($transference->getTarget(), $target);
        $this->assert->equals($transference->getFraction(), $this->toFraction($amount));
        $this->assert->equals($coin->getFraction(), $this->toFraction($amount));
    }

    public function Deposit_To($coins, $account) {
        $this->returned = $this->app->handle(new DepositCoins(
            new AccountIdentifier($account),
            $coins
        ));
    }

    public function aCoin_Of_WithSerial_Promising_By_IssuedBy($coin, $currency, $serial, $promise, $backer, $issuer) {
        $this->thoseCoins[$coin] = Coin::issue(new Promise($currency, $backer, $promise, $serial), new Signer($this->key, "private $issuer"));
    }

    public function _TransfersCoin_To_As($owner, $coin, $target, $newCoin) {
        $this->_Transfers_OfCoin_To_As($owner, 1, $coin, $target, $newCoin);
    }

    public function _Transfers_OfCoin_To_As($owner, $amount, $coin, $target, $newCoin) {
        $this->thoseCoins[$newCoin] = $this->thoseCoins[$coin]
            ->transfer($target, new Signer($this->key, "private $owner"), $this->toFraction($amount));
    }

    public function IDepositCoin_To($coin, $account) {
        $this->Deposit_To([$this->thoseCoins[$coin]], $account);
    }

    public function _WithdrawsOne_As($account, $currency, $coin) {
        $this->thoseCoins[$coin] = $this->_Withdraws($account, 1, $currency)[0];
    }
}