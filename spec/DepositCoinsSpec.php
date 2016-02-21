<?php
namespace spec\groupcash\bank;

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
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->_Adds_To('issuer', 'bart', 'foo');
    }

    function noCoins() {
        $this->scenario->when->_Deposits('bart', []);
        $this->scenario->then->allShouldBeFine();
    }

    function notTarget() {
        $this->scenario->tryThat->_Deposits('lisa', [
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'not lisa')
        ]);
        $this->scenario->then->itShouldFailWith('Coin 1 does not belong to account.');
    }

    function success() {
        $this->scenario->when->_Deposits('bart', [
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ]);
        $this->scenario->then->allShouldBeFine();
    }

    function alreadyDeposited() {
        $this->scenario->given->_Deposits('bart', [
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ]);
        $this->scenario->tryThat->_Deposits('bart', [
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ]);
        $this->scenario->then->itShouldFailWith('Coin 1 is already in account.');
    }

    function alreadyInAccount() {
        $this->scenario->given->_Declares_Of_By_For('issuer', 1, 'Promise', 'bart', 'foo');
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'bart');

        $this->scenario->tryThat->_Deposits('bart', [
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ]);
        $this->scenario->then->itShouldFailWith('Coin 1 is already in account.');
    }

    function notABacker() {
        $this->scenario->tryThat->_Deposits('not bart', [
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'not bart')
        ]);
        $this->scenario->then->itShouldFailWith('Could not validate coin 1: This backer was not added to this currency.');
    }

    function depositedCoinsCanBeSent() {
        $this->scenario->given->_Deposits('bart', [
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
        ]);
        $this->scenario->when->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->then->allShouldBeFine();
    }

    function notAnIssuer() {
        $this->scenario->tryThat->_Deposits('bart', [
            Coin::issue(new Promise('foo', 'bart', 'Promise', 1), new Signer(new FakeKeyService(), 'private not issuer'))
        ]);
        $this->scenario->then->itShouldFailWith('Could not validate coin 1: Coin could not be verified.');
    }

    function inconsistentCoin() {
        $this->scenario->tryThat->_Deposits('lisa', [
            $this->_WithSerial_Promising_IssuedTo('foo', 1, 'Promise', 'bart')
                ->transfer('lisa', new Signer(new FakeKeyService(), 'private not bart'))
        ]);
        $this->scenario->then->itShouldFailWith('Could not validate coin 1: Coin could not be verified.');
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