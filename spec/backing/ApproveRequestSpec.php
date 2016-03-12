<?php
namespace spec\groupcash\bank\backing;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Issuers can approve requests, the coins are then sent to the requesting account
 *
 * @property Scenario scenario <-
 */
class ApproveRequestSpec {

    function before() {
        $this->scenario->given->_HasAuthorized('foo', 'issuer');
        $this->scenario->given->ABacker_WasCreatedFor('backer', 'foo');
        $this->scenario->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->scenario->given->_HasRequested('bart', 1, 'foo');
    }

    function notAuthorized() {
        $this->scenario->tryThat->_ApprovesTheRequestOf_For('not issuer', 'bart', 'foo');
        $this->scenario->then->ItShouldFailWith('Not authorized for this currency.');
    }

    function noRequests() {
        $this->scenario->tryThat->_ApprovesTheRequestOf_For('issuer', 'not bart', 'foo');
        $this->scenario->then->ItShouldFailWith('There are not active requests for this account.');
    }

    function requestAlreadyCancelled() {
        $this->scenario->given->TheRequestBy_For_WasCancelled('bart', 'foo');
        $this->scenario->tryThat->_ApprovesTheRequestOf_For('issuer', 'bart', 'foo');
        $this->scenario->then->ItShouldFailWith('There are not active requests for this account.');
    }

    function requestAlreadyApproved() {
        $this->scenario->given->TheRequestBy_For_WasApprovedWithTheContributions('bart', 'foo', []);
        $this->scenario->tryThat->_ApprovesTheRequestOf_For('issuer', 'bart', 'foo');
        $this->scenario->then->ItShouldFailWith('There are not active requests for this account.');
    }

    function succeed() {
        $this->scenario->when->_ApprovesTheRequestOf_For('issuer', 'bart', 'foo');
        $this->scenario->then->TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions('bart', 'foo', 'issuer', [
            'backer' => 1
        ]);
        $this->scenario->then->ACoinWorth_ShouldBeSentFrom_To(1, 'foo', 'backer', 'bart');
    }

    function collectFromBackers() {
        $this->scenario->given->ABacker_WasCreatedFor('another backer', 'foo');
        $this->scenario->given->_HasIssued__To('issuer', 3, 'foo', 'another backer');

        $this->scenario->given->_HasRequested('lisa', 3, 'foo');
        $this->scenario->when->_ApprovesTheRequestOf_For('issuer', 'lisa', 'foo');
        $this->scenario->then->TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions('lisa', 'foo', 'issuer', [
            'backer' => 1,
            'another backer' => 2
        ]);
        $this->scenario->then->ACoinWorth_ShouldBeSentFrom_To(1, 'foo', 'backer', 'lisa');
        $this->scenario->then->ACoinWorth_ShouldBeSentFrom_To(2, 'foo', 'another backer', 'lisa');
    }

    function combineIssues() {
        $this->scenario->given->_HasIssued__To('issuer', 2, 'foo', 'backer');

        $this->scenario->given->_HasRequested('lisa', 3, 'foo');
        $this->scenario->when->_ApprovesTheRequestOf_For('issuer', 'lisa', 'foo');
        $this->scenario->then->TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions('lisa', 'foo', 'issuer', [
            'backer' => 3
        ]);
        $this->scenario->then->ACoinWorth_ShouldBeSentFrom_To(3, 'foo', 'backer', 'lisa');
    }

    function doNotSendSameCoinTwice() {
        $this->scenario->given->TheRequestBy_For_WasApprovedWithTheContributions('bart', 'foo', [
            'backer' => 1
        ]);
        $this->scenario->given->ABacker_WasCreatedFor('another backer', 'foo');
        $this->scenario->given->_HasIssued__To('issuer', 3, 'foo', 'another backer');
        $this->scenario->given->_HasRequested('bart', 1, 'foo');

        $this->scenario->when->_ApprovesTheRequestOf_For('issuer', 'bart', 'foo');
        $this->scenario->then->TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions('bart', 'foo', 'issuer', [
            'another backer' => 1
        ]);
        $this->scenario->then->ACoinWorth_ShouldBeSentFrom_To(1, 'foo', 'another backer', 'bart');
    }
}