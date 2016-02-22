<?php
namespace spec\groupcash\bank\trading;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins from other applications can be deposited to an account to be used inside bank.
 *
 * @property Scenario scenario <-
 */
class DepositCoinsSpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->ICreateABacker('bart');
        $this->scenario->given->_Adds_To('issuer', 'bart', 'foo');
    }

    function noCoins() {
        $this->scenario->when->IDepositNoCoinsTo('bart');
        $this->scenario->then->allShouldBeFine();
    }

    function notTarget() {
        $this->scenario->given->aCoin_Of_WithSerial_Promising_By_IssuedBy('one', 'foo', 1, 'Promise', 'not lisa', 'issuer');
        $this->scenario->tryThat->IDepositCoin_To('one', 'lisa');
        $this->scenario->then->itShouldFailWith('Coin was not transferred to this account.');
    }

    function success() {
        $this->scenario->given->aCoin_Of_WithSerial_Promising_By_IssuedBy('one', 'foo', 1, 'Promise', 'bart', 'issuer');
        $this->scenario->tryThat->IDepositCoin_To('one', 'bart');
        $this->scenario->then->allShouldBeFine();
    }

    function alreadyInAccount() {
        $this->scenario->given->_Declares_Of_By_For('issuer', 1, 'Promise', 'bart', 'foo');
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'bart');

        $this->scenario->given->aCoin_Of_WithSerial_Promising_By_IssuedBy('one', 'foo', 1, 'Promise', 'bart', 'issuer');
        $this->scenario->tryThat->IDepositCoin_To('one', 'bart');
        $this->scenario->then->itShouldFailWith('Coin is already in account.');
    }

    function alreadyDeposited() {
        $this->scenario->given->aCoin_Of_WithSerial_Promising_By_IssuedBy('one', 'foo', 1, 'Promise', 'bart', 'issuer');
        $this->scenario->given->IDepositCoin_To('one', 'bart');
        $this->scenario->tryThat->IDepositCoin_To('one', 'bart');
        $this->scenario->then->itShouldFailWith('Coin is already in account.');
    }

    function depositedCoinsCanBeSent() {
        $this->scenario->given->aCoin_Of_WithSerial_Promising_By_IssuedBy('one', 'foo', 1, 'Promise', 'bart', 'issuer');
        $this->scenario->given->IDepositCoin_To('one', 'bart');
        $this->scenario->when->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->then->allShouldBeFine();
    }
}