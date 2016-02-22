<?php
namespace spec\groupcash\bank\trading;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins can be sent to another account, they will be delivered and validated and are then available to be sent again.
 *
 * @property Scenario scenario <-
 */
class SendCoinsSpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->ICreateABacker('bart');
        $this->scenario->given->_Adds_To('issuer', 'bart', 'foo');
        $this->scenario->given->_Declares_Of_By_For('issuer', 5, 'My Promise', 'bart', 'foo');
    }

    function noCoins() {
        $this->scenario->tryThat->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->then->itShouldFailWith('No coins of this currency available in account.');
    }

    function notEnoughCoins() {
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'bart');
        $this->scenario->tryThat->_Sends__To('bart', 2, 'foo', 'lisa');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');
    }

    function singleCoin() {
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'bart');
        $this->scenario->when->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->then->_Sends__To('lisa', 1, 'foo', 'homer');
        $this->scenario->then->allShouldBeFine();
    }

    function sendAgain() {
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'bart');
        $this->scenario->when->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->when->_Sends__To('lisa', 1, 'foo', 'homer');
        $this->scenario->when->_Sends__To('homer', 1, 'foo', 'marge');
        $this->scenario->when->_Sends__To('marge', 1, 'foo', 'maggie');
        $this->scenario->then->allShouldBeFine();
    }

    function subtractSentCoins() {
        $this->scenario->given->_issues__to('issuer', 3, 'foo', 'bart');
        $this->scenario->given->_Sends__To('bart', 3, 'foo', 'lisa');

        $this->scenario->tryThat->_Sends__To('bart', 1, 'foo', 'marge');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');
    }

    function multipleSendings() {
        $this->scenario->given->_issues__to('issuer', 3, 'foo', 'bart');

        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'homer');
        $this->scenario->when->_Sends__To('bart', 1, 'foo', 'marge');

        $this->scenario->then->allShouldBeFine();
    }

    function complexSending() {
        $this->scenario->given->_issues__to('issuer', 3, 'foo', 'bart');

        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'homer');
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'marge');

        $this->scenario->given->_Sends__To('lisa', 1, 'foo', 'maggie');
        $this->scenario->given->_Sends__To('homer', 1, 'foo', 'maggie');
        $this->scenario->given->_Sends__To('marge', 1, 'foo', 'maggie');

        $this->scenario->given->_Sends__To('maggie', 3, 'foo', 'bart');
        $this->scenario->given->_Sends__To('bart', 3, 'foo', 'apu');

        $this->scenario->then->allShouldBeFine();
    }

    function multipleCurrencies() {
        $this->scenario->given->_Authorizes('bar', 'issuer');
        $this->scenario->given->_Adds_To('issuer', 'bart', 'bar');
        $this->scenario->given->_Declares_Of_By_For('issuer', 1, 'Other Promise', 'bart', 'bar');

        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'bart');
        $this->scenario->given->_issues__to('issuer', 1, 'bar', 'bart');

        $this->scenario->tryThat->_Sends__To('bart', 2, 'foo', 'lisa');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');

        $this->scenario->tryThat->_Sends__To('bart', 2, 'bar', 'lisa');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');

        $this->scenario->when->_Sends__To('bart', 1, 'bar', 'lisa');
        $this->scenario->when->_Sends__To('bart', 1, 'foo', 'lisa');
    }

    function fractionOfCoin() {
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'bart');

        $this->scenario->when->_Sends__To('bart', .5, 'foo', 'lisa');
        $this->scenario->when->_Sends__To('bart', .5, 'foo', 'homer');

        $this->scenario->tryThat->_Sends__To('bart', .01, 'foo', 'marge');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');

        $this->scenario->when->_Sends__To('lisa', .5, 'foo', 'marge');
        $this->scenario->tryThat->_Sends__To('lisa', .01, 'foo', 'maggie');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');

        $this->scenario->tryThat->_Sends__To('homer', .6, 'foo', 'maggie');
        $this->scenario->then->itShouldFailWith('Not sufficient coins of this currency available in account.');
    }

    function combineFractions() {
        $this->scenario->given->_issues__to('issuer', 3, 'foo', 'bart');

        $this->scenario->given->_Sends__To('bart', .5, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', .5, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', .5, 'foo', 'lisa');

        $this->scenario->when->_Sends__To('lisa', 1.5, 'foo', 'marge');
        $this->scenario->then->allShouldBeFine();
    }

    function wholeAndFractions() {
        $this->scenario->given->_issues__to('issuer', 5, 'foo', 'bart');
        $this->scenario->given->_Sends__To('bart', 5, 'foo', 'lisa');

        $this->scenario->when->_Sends__To('lisa', 2, 'foo', 'homer');
        $this->scenario->when->_Sends__To('lisa', 2.5, 'foo', 'marge');
        $this->scenario->then->allShouldBeFine();
    }
}