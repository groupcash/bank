<?php
namespace spec\groupcash\bank\trading;

use groupcash\php\model\Coin;
use groupcash\php\model\Promise;
use groupcash\php\model\Signer;
use spec\groupcash\bank\fakes\FakeKeyService;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins can be deposited to an account and become available after being verified and validated.
 *
 * @property Scenario scenario <-
 */
class DepositCoinsSpec {

    function before() {
        $this->scenario->blockedBy('Confirmation');

        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->ICreateABacker('bart');
        $this->scenario->given->_Adds_To('issuer', 'bart', 'foo');
    }

    function noCoins() {
        $this->scenario->when->Deposit_To([], 'bart');
        $this->scenario->then->allShouldBeFine();
    }

    function notTarget() {
        $this->scenario->tryThat->Deposit_To([
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'not lisa')
        ], 'lisa');
        $this->scenario->then->itShouldFailWith('Coin 1 does not belong to account.');
    }

    function success() {
        $this->scenario->when->Deposit_To([
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ], 'bart');
        $this->scenario->then->allShouldBeFine();
    }

    function alreadyDeposited() {
        $this->scenario->given->Deposit_To([
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ], 'bart');
        $this->scenario->tryThat->Deposit_To([
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ], 'bart');
        $this->scenario->then->itShouldFailWith('Coin 1 is already in account.');
    }

    function alreadyInAccount() {
        $this->scenario->given->_Declares_Of_By_For('issuer', 1, 'Promise', 'bart', 'foo');
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'bart');

        $this->scenario->tryThat->Deposit_To([
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ], 'bart');
        $this->scenario->then->itShouldFailWith('Coin 1 is already in account.');
    }

    function notABacker() {
        $this->scenario->tryThat->Deposit_To([
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'not bart')
        ], 'not bart');
        $this->scenario->then->itShouldFailWith('This backer was not added to this currency.');
    }

    function depositedCoinsCanBeSent() {
        $this->scenario->given->Deposit_To([
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ], 'bart');
        $this->scenario->when->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->then->allShouldBeFine();
    }

    function notAnIssuer() {
        $this->scenario->tryThat->Deposit_To([
            Coin::issue(new Promise('foo', 'bart', 'Promise', 1), new Signer(new FakeKeyService(), 'private not issuer'))
        ], 'bart');
        $this->scenario->then->itShouldFailWith('The issuer is not authorized.');
    }

    function inconsistentCoin() {
        $this->scenario->tryThat->Deposit_To([
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
                ->transfer('lisa', new Signer(new FakeKeyService(), 'private not bart'))
        ], 'lisa');
        $this->scenario->then->itShouldFailWith('Signed by non-owner [not bart].');
    }

    private function _WithSerial_Promising_IssuedTo($currency, $serial, $promise, $backer) {
        return Coin::issue(new Promise(
            $currency,
            $backer,
            $promise,
            $serial
        ), new Signer(new FakeKeyService(), 'private issuer'));
    }
}