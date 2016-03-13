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

    private function handle($command) {
        $this->specification->when($command);
    }

    public function IGenerateAnAccount() {
        $this->handle(new GenerateAccount());
    }

    public function ICreateAnAccountWithThePassword($password) {
        $this->handle(new GenerateAccount($password));
    }

    public function _EstablishesACurrencyWithTheRules($currency, $rules) {
        $this->handle(new EstablishCurrency(
            $this->auth($currency),
            $rules));
    }

    public function _RegistersTheCurrencyUnderTheName($currency, $name) {
        $this->handle(new RegisterCurrency(
            $this->auth($currency),
            $name));
    }

    public function IRegister_AsBackerWithTheName($address, $name) {
        $this->handle(new RegisterBacker(
            new AccountIdentifier($this->enc($address)),
            $name
        ));
    }

    public function IRegister_AsBackerWithTheName_AndTheDetails($address, $name, $details) {
        $this->handle(new RegisterBacker(
            new AccountIdentifier($this->enc($address)),
            $name,
            $details
        ));
    }

    public function _CreatesANewBackerFor($issuer, $currency) {
        $this->handle(new CreateBacker(
            $this->auth($issuer),
            new CurrencyIdentifier($this->enc($currency))
        ));
    }

    public function _Authorizes($currency, $issuer) {
        $this->handle(new AuthorizeIssuer(
            $this->auth($currency),
            new AccountIdentifier($this->enc($issuer))
        ));
    }

    private function auth($address) {
        return new Authentication(new Binary("$address key"));
    }

    public function _Issues__To_BackedBy($issuer, $value, $currency, $backer, $description) {
        $this->handle(new IssueCoin(
            $this->auth($issuer),
            new CurrencyIdentifier($this->enc($currency)),
            $description,
            new Fraction($value),
            new BackerIdentifier($this->enc($backer))
        ));
    }

    public function _Sends__To($owner, $value, $currency, $target) {
        $this->handle(new SendCoins(
            $this->auth($owner),
            new AccountIdentifier($this->enc($target)),
            new CurrencyIdentifier($this->enc($currency)),
            new Fraction($value)
        ));
    }

    public function _Requests($account, $value, $currency) {
        $this->handle(new RequestCoins(
            $this->auth($account),
            new CurrencyIdentifier($this->enc($currency)),
            new Fraction($value)
        ));
    }

    public function _CancelsTheRequestOf_For($issuer, $account, $currency) {
        $this->handle(new CancelRequest(
            $this->auth($issuer),
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($account))
        ));
    }

    public function _ApprovesTheRequestOf_For($issuer, $account, $currency) {
        $this->handle(new ApproveRequest(
            $this->auth($issuer),
            new CurrencyIdentifier($this->enc($currency)),
            new AccountIdentifier($this->enc($account))
        ));
    }
}