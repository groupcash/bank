<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\Specification;
use groupcash\bank\ApproveRequest;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CancelRequest;
use groupcash\bank\CreateBacker;
use groupcash\bank\EstablishCurrency;
use groupcash\bank\GenerateAccount;
use groupcash\bank\IssueCoin;
use groupcash\bank\ListTransactions;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\RegisterBacker;
use groupcash\bank\RegisterCurrency;
use groupcash\bank\RequestCoins;
use groupcash\bank\SendCoins;
use groupcash\php\model\signing\Binary;
use groupcash\php\model\value\Fraction;

class SpecificationCapabilities {

    /** @var Specification */
    private $specification;

    /**
     * @param Specification $specification
     */
    public function __construct(Specification $specification) {
        $this->specification = $specification;
    }

    private function enc($address) {
        return base64_encode($address);
    }

    public function IGenerateAnAccount() {
        $this->specification->when(new GenerateAccount());
    }

    public function ICreateAnAccountWithThePassword($password) {
        $this->specification->when(new GenerateAccount($password));
    }

    public function _EstablishesACurrencyWithTheRules($currency, $rules) {
        $this->specification->when(new EstablishCurrency(
            $this->auth($currency),
            $rules));
    }

    public function _RegistersTheCurrencyUnderTheName($currency, $name) {
        $this->specification->when(new RegisterCurrency(
            $this->auth($currency),
            $name));
    }

    public function IRegister_AsBackerWithTheName($address, $name) {
        $this->specification->when(new RegisterBacker(
            new AccountIdentifier($this->enc($address)),
            $name
        ));
    }

    public function IRegister_AsBackerWithTheName_AndTheDetails($address, $name, $details) {
        $this->specification->when(new RegisterBacker(
            new AccountIdentifier($this->enc($address)),
            $name,
            $details
        ));
    }

    public function _CreatesANewBackerFor($issuer, $currency) {
        $this->specification->when(new CreateBacker(
            $this->auth($issuer),
            new CurrencyIdentifier($this->enc($currency))
        ));
    }

    public function _Authorizes($currency, $issuer) {
        $this->specification->when(new AuthorizeIssuer(
            $this->auth($currency),
            new AccountIdentifier($this->enc($issuer))
        ));
    }

    private function auth($address) {
        return new Authentication(new Binary("$address key"));
    }

    public function _Issues__To_BackedBy($issuer, $value, $currency, $backer, $description) {
        $this->specification->when(new IssueCoin(
            $this->auth($issuer),
            new CurrencyIdentifier($this->enc($currency)),
            $description,
            new Fraction($value),
            new BackerIdentifier($this->enc($backer))
        ));
    }

    public function _Sends__To($owner, $value, $currency, $target) {
        $this->_Sends__To_WithTheSubject($owner, $value, $currency, $target, null);
    }

    public function _Sends__To_WithTheSubject($owner, $value, $currency, $target, $subject) {
        $this->specification->when(new SendCoins(
            $this->auth($owner),
            new AccountIdentifier($this->enc($target)),
            new CurrencyIdentifier($this->enc($currency)),
            new Fraction($value),
            $subject
        ));
    }

    public function _Requests($account, $value, $currency) {
        $this->specification->when(new RequestCoins(
            $this->auth($account),
            new CurrencyIdentifier($this->enc($currency)),
            new Fraction($value)
        ));
    }

    public function _CancelsTheRequestOf_For($issuer, $account, $currency) {
        $this->specification->when(new CancelRequest(
            $this->auth($issuer),
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($account))
        ));
    }

    public function _ApprovesTheRequestOf_For($issuer, $account, $currency) {
        $this->specification->when(new ApproveRequest(
            $this->auth($issuer),
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($account))
        ));
    }

    public function _ListsTheirTransactions($account) {
        $this->specification->when(new ListTransactions(
            $this->auth($account)
        ));
    }
}