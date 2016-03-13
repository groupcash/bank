<?php
namespace spec\groupcash\bank\backing;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins can be requested from a currency and sent by issuers
 */
class RequestCoinSpec extends Scenario {

    function before() {
        parent::before();
        $this->given->ABacker_WasCreatedFor('backer', 'foo');
    }

    function noIssuedCoins() {
        $this->tryThat->_Requests('bart', 1, 'foo');
        $this->then->ItShouldFailWith('Value exceeds available coins: 0');
    }

    function notEnoughCoinsAvailable() {
        $this->given->_HasIssued__To('issuer', 2, 'foo', 'backer');
        $this->tryThat->_Requests('bart', 3, 'foo');
        $this->then->ItShouldFailWith('Value exceeds available coins: 2');
    }

    function succeed() {
        $this->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->when->_Requests('bart', 1, 'foo');
        $this->then->ThereShouldBe__RequestedBy(1, 'foo', 'bart');
    }

    function alreadyOngoingRequest() {
        $this->given->_HasRequested('bart', 1, 'foo');
        $this->tryThat->_Requests('bart', 1, 'foo');
        $this->then->ItShouldFailWith('There is already a request from this account for this currency.');
    }

    function requestForOtherCurrency() {
        $this->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->given->_HasRequested('bart', 1, 'not foo');
        $this->when->_Requests('bart', 1, 'foo');
        $this->then->ThereShouldBe__RequestedBy(1, 'foo', 'bart');
    }

    function requestFromOtherAccount() {
        $this->given->_HasIssued__To('issuer', 2, 'foo', 'backer');
        $this->given->_HasRequested('lisa', 1, 'not foo');
        $this->when->_Requests('bart', 1, 'foo');
        $this->then->ThereShouldBe__RequestedBy(1, 'foo', 'bart');
    }

    function combineCoins() {
        $this->given->ABacker_WasCreatedFor('other backer', 'foo');
        $this->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->given->_HasIssued__To('issuer', 1, 'foo', 'other backer');
        $this->when->_Requests('bart', 2, 'foo');
        $this->then->ThereShouldBe__RequestedBy(2, 'foo', 'bart');
    }

    function coinsIssuedToExternalBackers() {
        $this->given->_HasIssued__To('issuer', 1, 'foo', 'external backer');
        $this->tryThat->_Requests('bart', 1, 'foo');
        $this->then->ItShouldFailWith('Value exceeds available coins: 0');
    }

    function requestedCoinsAreNotAvailable() {
        $this->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->given->_HasRequested('bart', 1, 'foo');
        $this->tryThat->_Requests('lisa', 1, 'foo');
        $this->then->ItShouldFailWith('Value exceeds available coins: 0');
    }

    function coinsOfCancelledRequestsAreNotAvailable() {
        $this->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->given->_HasRequested('bart', 1, 'foo');
        $this->given->TheRequestBy_For_WasCancelled('bart', 'foo');

        $this->when->_Requests('lisa', 1, 'foo');
        $this->then->ThereShouldBe__RequestedBy(1, 'foo', 'lisa');
    }
}