<?php
namespace spec\groupcash\bank;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins available in an account can be withdrawn to be used in another groupcash application.
 *
 * @property Scenario scenario <-
 */
class WithdrawCoinsSpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->ICreateABacker('bart');
        $this->scenario->given->_Adds_To('issuer', 'bart', 'foo');
        $this->scenario->given->_Declares_Of_By_For('issuer', 3, 'Cow', 'bart', 'foo');
        $this->scenario->given->_issues__to('issuer', 3, 'foo', 'bart');
    }

    function noCoinsAvailable() {
        $this->scenario->tryThat->_Withdraws('lisa', 1, 'foo');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');
    }

    function notEnoughCoinsAvailable() {
        $this->scenario->tryThat->_Withdraws('bart', 4, 'foo');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');
    }

    function wholeCoins() {
        $this->scenario->when->_Withdraws('bart', 3, 'foo');
        $this->scenario->then->thereShouldBe_Coins(3);
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(1, 1, 'foo', 'Cow', 1, 'bart', 'bart');
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(2, 1, 'foo', 'Cow', 2, 'bart', 'bart');
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(3, 1, 'foo', 'Cow', 3, 'bart', 'bart');
    }

    function transferredCoins() {
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('lisa', 1, 'foo', 'homer');

        $this->scenario->when->_Withdraws('homer', 1, 'foo');

        $this->scenario->then->thereShouldBe_Coins(1);
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(1, 1, 'foo', 'Cow', 1, 'bart', 'homer');
    }

    function fractionOfCoins() {
        $this->scenario->when->_Withdraws('bart', 1.5, 'foo');
        $this->scenario->then->thereShouldBe_Coins(2);
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(1, 1, 'foo', 'Cow', 1, 'bart', 'bart');
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(2, .5, 'foo', 'Cow', 2, 'bart', 'bart');
    }

    function collectFractions() {
        $this->scenario->given->_Sends__To('bart', .5, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', .5, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', .5, 'foo', 'lisa');

        $this->scenario->when->_Withdraws('lisa', 1.5, 'foo');

        $this->scenario->then->thereShouldBe_Coins(3);
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(1, .5, 'foo', 'Cow', 1, 'bart', 'lisa');
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(2, .5, 'foo', 'Cow', 1, 'bart', 'lisa');
        $this->scenario->then->coin_ShouldBe__Promising__By_TransferredTo(3, .5, 'foo', 'Cow', 2, 'bart', 'lisa');
    }

    function withdrawnCoinsAreNotAvailable() {
        $this->scenario->given->_Withdraws('bart', 3, 'foo');
        $this->scenario->tryThat->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');
    }
}