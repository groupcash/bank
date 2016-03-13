<?php
namespace spec\groupcash\bank\backing;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Issuers can approve requests, the coins are then sent to the requesting account
 */
class ApproveRequestSpec extends Scenario {

    function before() {
        parent::before();
        $this->given->_HasAuthorized('foo', 'issuer');
        $this->given->ABacker_WasCreatedFor('backer', 'foo');
        $this->given->_HasIssued__To('issuer', 1, 'foo', 'backer');
        $this->given->_HasRequested('bart', 1, 'foo');
    }

    function notAuthorized() {
        $this->tryThat->_ApprovesTheRequestOf_For('not issuer', 'bart', 'foo');
        $this->then->ItShouldFailWith('Not authorized for this currency.');
    }

    function noRequests() {
        $this->tryThat->_ApprovesTheRequestOf_For('issuer', 'not bart', 'foo');
        $this->then->ItShouldFailWith('There are not active requests for this account.');
    }

    function requestAlreadyCancelled() {
        $this->given->TheRequestBy_For_WasCancelled('bart', 'foo');
        $this->tryThat->_ApprovesTheRequestOf_For('issuer', 'bart', 'foo');
        $this->then->ItShouldFailWith('There are not active requests for this account.');
    }

    function requestAlreadyApproved() {
        $this->given->TheRequestBy_For_WasApprovedWithTheContributions('bart', 'foo', []);
        $this->tryThat->_ApprovesTheRequestOf_For('issuer', 'bart', 'foo');
        $this->then->ItShouldFailWith('There are not active requests for this account.');
    }

    function succeed() {
        $this->when->_ApprovesTheRequestOf_For('issuer', 'bart', 'foo');
        $this->then->TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions('bart', 'foo', 'issuer', [
            'backer' => 1
        ]);
        $this->then->ACoinWorth_ShouldBeSentFrom_To(1, 'foo', 'backer', 'bart');
    }

    function collectFromBackers() {
        $this->given->ABacker_WasCreatedFor('another backer', 'foo');
        $this->given->_HasIssued__To('issuer', 3, 'foo', 'another backer');

        $this->given->_HasRequested('lisa', 3, 'foo');
        $this->when->_ApprovesTheRequestOf_For('issuer', 'lisa', 'foo');
        $this->then->TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions('lisa', 'foo', 'issuer', [
            'backer' => 1,
            'another backer' => 2
        ]);
        $this->then->ACoinWorth_ShouldBeSentFrom_To(1, 'foo', 'backer', 'lisa');
        $this->then->ACoinWorth_ShouldBeSentFrom_To(2, 'foo', 'another backer', 'lisa');
    }

    function combineIssues() {
        $this->given->_HasIssued__To('issuer', 2, 'foo', 'backer');

        $this->given->_HasRequested('lisa', 3, 'foo');
        $this->when->_ApprovesTheRequestOf_For('issuer', 'lisa', 'foo');
        $this->then->TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions('lisa', 'foo', 'issuer', [
            'backer' => 3
        ]);
        $this->then->ACoinWorth_ShouldBeSentFrom_To(3, 'foo', 'backer', 'lisa');
    }

    function doNotSendSameCoinTwice() {
        $this->given->TheRequestBy_For_WasApprovedWithTheContributions('bart', 'foo', [
            'backer' => 1
        ]);
        $this->given->ABacker_WasCreatedFor('another backer', 'foo');
        $this->given->_HasIssued__To('issuer', 3, 'foo', 'another backer');
        $this->given->_HasRequested('bart', 1, 'foo');

        $this->when->_ApprovesTheRequestOf_For('issuer', 'bart', 'foo');
        $this->then->TheRequestOf_For_ShouldBeApprovedBy_WithTheContributions('bart', 'foo', 'issuer', [
            'another backer' => 1
        ]);
        $this->then->ACoinWorth_ShouldBeSentFrom_To(1, 'foo', 'another backer', 'bart');
    }
}