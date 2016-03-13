<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins can be sent from one account to another.
 */
class SendCoinSpec extends Scenario {

    function before() {
        parent::before();
        $this->given->_HasReceivedACoin_Worth('bart', 'foo', 3, 'coin');
    }

    function noCoins() {
        $this->tryThat->_Sends__To('not bart', 1, 'coin', 'lisa');
        $this->then->ItShouldFailWith('No coins of currency in account.');
    }

    function coinsInDifferentCurrency() {
        $this->tryThat->_Sends__To('bart', 1, 'not coin', 'lisa');
        $this->then->ItShouldFailWith('No coins of currency in account.');
    }

    function notEnoughCoins() {
        $this->tryThat->_Sends__To('bart', 4, 'coin', 'lisa');
        $this->then->ItShouldFailWith('Not enough coins of currency in account.');
    }

    function exactMatch() {
        $this->when->_Sends__To('bart', 3, 'coin', 'lisa');
        $this->then->Coin_Worth_ShouldBeSentFrom_To('foo', 3, 'coin', 'bart', 'lisa');
        $this->then->_ShouldReceiveACoinWorth('lisa', 3, 'coin');
    }

    function sentCoinsAreGone() {
        $this->given->_HasSentACoin_Worth__To('bart', 'foo', 3, 'coin', 'lisa');
        $this->tryThat->_Sends__To('bart', 3, 'coin', 'lisa');
        $this->then->ItShouldFailWith('Not enough coins of currency in account.');
    }

    function keepChange() {
        $this->when->_Sends__To('bart', 2, 'coin', 'lisa');

        $this->then->Coin_Worth_ShouldBeSentFrom_To('foo', 3, 'coin', 'bart', 'lisa');
        $this->then->_ShouldReceiveACoinWorth('lisa', 2, 'coin');

        $this->then->Coin_Worth_ShouldBeSentFrom_To('foo', 3, 'coin', 'bart', 'bart');
        $this->then->_ShouldReceiveACoinWorth('bart', 1, 'coin');
    }

    function combineCoins() {
        $this->given->_HasReceivedACoin_Worth('bart', 'bar', 2, 'coin');
        $this->when->_Sends__To('bart', 5, 'coin', 'lisa');

        $this->then->Coin_Worth_ShouldBeSentFrom_To('foo', 3, 'coin', 'bart', 'lisa');
        $this->then->Coin_Worth_ShouldBeSentFrom_To('bar', 2, 'coin', 'bart', 'lisa');
        $this->then->_ShouldReceiveACoinWorth('lisa', 5, 'coin');
    }

    function withSubject() {
        $this->when->_Sends__To_WithTheSubject('bart', 3, 'coin', 'lisa', 'Foo!');
        $this->then->ACoinWorth_ShouldBeSentFrom_To_WithTheSubject(3, 'coin', 'bart', 'lisa', 'Foo!');
        $this->then->_ShouldReceiveACoinWorth__WithTheSubject('lisa', 3, 'coin', 'Foo!');
    }
}