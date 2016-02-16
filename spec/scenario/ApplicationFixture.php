<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\AddBacker;
use groupcash\bank\app\ApplicationService;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CreateAccount;
use groupcash\bank\DeclarePromise;
use groupcash\bank\IssueCoins;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\SendCoins;
use groupcash\php\Groupcash;
use groupcash\php\model\Fraction;
use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use spec\groupcash\bank\fakes\FakeCryptography;
use spec\groupcash\bank\fakes\FakeEventStore;
use spec\groupcash\bank\fakes\FakeKeyService;

class ApplicationFixture {

    /** @var FakeEventStore */
    private $store;

    /** @var FakeKeyService */
    private $key;

    /** @var ApplicationService */
    private $app;

    /** @var ExceptionFixture */
    private $except;

    /** @var Assert */
    private $assert;

    /** @var mixed */
    private $returned;

    public function __construct(Assert $assert, ExceptionFixture $except) {
        $this->assert = $assert;
        $this->except = $except;

        $this->key = new FakeKeyService();
        $this->store = new FakeEventStore();
        $this->app = new ApplicationService(
            $this->store,
            new FakeCryptography(),
            new Groupcash(
                $this->key
            ),
            'secret'
        );
    }

    public function _Sends__th_To($owner, $nominator, $denominator, $currency, $target) {
        $this->app->handle(new SendCoins(
            new Authentication("private $owner"),
            new Fraction($nominator, $denominator),
            new CurrencyIdentifier($currency),
            new AccountIdentifier($target)
        ));
    }

    public function _Sends__To($owner, $amount, $currency, $target) {
        $this->app->handle(new SendCoins(
            new Authentication("private $owner"),
            $amount,
            new CurrencyIdentifier($currency),
            new AccountIdentifier($target)
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
            new AccountIdentifier($backer)
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

    public function _Adds_To($issuer, $backer, $currency) {
        $this->key->nextKey = $backer;

        $this->app->handle(new AddBacker(
            new Authentication("private $issuer"),
            new CurrencyIdentifier($currency)
        ));
    }

    public function _Declares_Of_By_For($issuer, $limit, $promise, $backer, $currency) {
        $this->app->handle(new DeclarePromise(
            new Authentication("private $issuer"),
            new AccountIdentifier($backer),
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

    public function isShouldReturn($value) {
        $this->assert->equals($this->returned, $value);
    }
}