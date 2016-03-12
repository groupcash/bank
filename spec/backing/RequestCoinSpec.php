<?php
namespace spec\groupcash\bank\backing;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins can be requested from a currency and sent by issuers
 *
 * @property Scenario scenario <-
 */
class RequestCoinSpec {

    function before() {
        $this->scenario->given->ABacker_WasCreatedFor('backer', 'foo');
    }

    function noIssuedCoins() {
        $this->scenario->tryThat->_Requests('bart', 1, 'foo');
        $this->scenario->then->ItShouldFailWith('Value exceeds available coins: 0');
    }

    function notEnoughCoinsAvailable() {
        $this->scenario->given->_HasIssued__To('issuer', 2, 'foo', 'backer');
        $this->scenario->tryThat->_Requests('bart', 3, 'foo');
        $this->scenario->then->ItShouldFailWith('Value exceeds available coins: 2');
    }

    function succeed() {
        $this->scenario->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->scenario->when->_Requests('bart', 1, 'foo');
        $this->scenario->then->ThereShouldBe__RequestedBy(1, 'foo', 'bart');
    }

    function alreadyOngoingRequest() {
        $this->scenario->given->_HasRequested('bart', 1, 'foo');
        $this->scenario->tryThat->_Requests('bart', 1, 'foo');
        $this->scenario->then->ItShouldFailWith('There is already a request from this account for this currency.');
    }

    function requestForOtherCurrency() {
        $this->scenario->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->scenario->given->_HasRequested('bart', 1, 'not foo');
        $this->scenario->when->_Requests('bart', 1, 'foo');
        $this->scenario->then->ThereShouldBe__RequestedBy(1, 'foo', 'bart');
    }

    function requestFromOtherAccount() {
        $this->scenario->given->_HasIssued__To('issuer', 2, 'foo', 'backer');
        $this->scenario->given->_HasRequested('lisa', 1, 'not foo');
        $this->scenario->when->_Requests('bart', 1, 'foo');
        $this->scenario->then->ThereShouldBe__RequestedBy(1, 'foo', 'bart');
    }

    function combineCoins() {
        $this->scenario->given->ABacker_WasCreatedFor('other backer', 'foo');
        $this->scenario->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->scenario->given->_HasIssued__To('issuer', 1, 'foo', 'other backer');
        $this->scenario->when->_Requests('bart', 2, 'foo');
        $this->scenario->then->ThereShouldBe__RequestedBy(2, 'foo', 'bart');
    }

    function coinsIssuedToExternalBackers() {
        $this->scenario->given->_HasIssued__To('issuer', 1, 'foo', 'external backer');
        $this->scenario->tryThat->_Requests('bart', 1, 'foo');
        $this->scenario->then->ItShouldFailWith('Value exceeds available coins: 0');
    }

    function requestedCoinsAreNotAvailable() {
        $this->scenario->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->scenario->given->_HasRequested('bart', 1, 'foo');
        $this->scenario->tryThat->_Requests('lisa', 1, 'foo');
        $this->scenario->then->ItShouldFailWith('Value exceeds available coins: 0');
    }

    function coinsOfCancelledRequestsAreNotAvailable() {
        $this->scenario->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->scenario->given->_HasRequested('bart', 1, 'foo');
        $this->scenario->given->TheRequestBy_For_WasCancelled('bart', 'foo');

        $this->scenario->when->_Requests('lisa', 1, 'foo');
        $this->scenario->then->ThereShouldBe__RequestedBy(1, 'foo', 'lisa');
    }
}