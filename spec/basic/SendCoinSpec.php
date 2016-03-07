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
        $this->scenario->given->_HasReceivedACoin_Worth('bart', 'foo', 3, 'coin');
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
        $this->scenario->then->Coin_Worth_ShouldBeSentFrom_To('foo', 3, 'coin', 'bart', 'lisa');
        $this->scenario->then->_ShouldReceiveACoinWorth('lisa', 3, 'coin');
    }

    function sentCoinsAreGone() {
        $this->scenario->given->_HasSentACoin_Worth__To('bart', 'foo', 3, 'coin', 'lisa');
        $this->scenario->tryThat->_Sends__To('bart', 3, 'coin', 'lisa');
        $this->scenario->then->ItShouldFailWith('Not enough coins of currency in account.');
    }

    function keepChange() {
        $this->scenario->when->_Sends__To('bart', 2, 'coin', 'lisa');

        $this->scenario->then->Coin_Worth_ShouldBeSentFrom_To('foo', 3, 'coin', 'bart', 'lisa');
        $this->scenario->then->_ShouldReceiveACoinWorth('lisa', 2, 'coin');

        $this->scenario->then->Coin_Worth_ShouldBeSentFrom_To('foo', 3, 'coin', 'bart', 'bart');
        $this->scenario->then->_ShouldReceiveACoinWorth('bart', 1, 'coin');
    }

    function combineCoins() {
        $this->scenario->given->_HasReceivedACoin_Worth('bart', 'bar', 2, 'coin');
        $this->scenario->when->_Sends__To('bart', 5, 'coin', 'lisa');

        $this->scenario->then->Coin_Worth_ShouldBeSentFrom_To('foo', 3, 'coin', 'bart', 'lisa');
        $this->scenario->then->Coin_Worth_ShouldBeSentFrom_To('bar', 2, 'coin', 'bart', 'lisa');
        $this->scenario->then->_ShouldReceiveACoinWorth('lisa', 5, 'coin');
    }
}