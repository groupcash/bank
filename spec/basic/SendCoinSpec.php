<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins can be sent from one account to another.
 *
 * @property Scenario scenario <-
 */
class SendCoinSpec {

    function before() {
        $this->scenario->given->_HasReceived('bart', 3, 'coin');
    }

    function noCoins() {
        $this->scenario->tryThat->_Sends__To('not bart', 1, 'coin', 'lisa');
        $this->scenario->then->ItShouldFailWith('No coins of currency in account.');
    }

    function coinsInDifferentCurrency() {
        $this->scenario->tryThat->_Sends__To('bart', 1, 'not coin', 'lisa');
        $this->scenario->then->ItShouldFailWith('No coins of currency in account.');
    }

    function notEnoughCoins() {
        $this->scenario->tryThat->_Sends__To('bart', 4, 'coin', 'lisa');
        $this->scenario->then->ItShouldFailWith('Not enough coins of currency in account.');
    }

    function exactMatch() {
        $this->scenario->when->_Sends__To('bart', 3, 'coin', 'lisa');
        $this->scenario->then->_CoinWorth_ShouldBeSentTo_By(1, 3, 'coin', 'lisa', 'bart');
        $this->scenario->then->_ShouldReceive('lisa', 3, 'coin');
    }

    function sentCoinsAreGone() {
        $this->scenario->given->_HasSent__To('bart', 3, 'coin', 'lisa');
        $this->scenario->tryThat->_Sends__To('bart', 3, 'coin', 'lisa');
        $this->scenario->then->ItShouldFailWith('Not enough coins of currency in account.');
    }

    function keepChange() {
    }

    function combineCoins() {
    }
}