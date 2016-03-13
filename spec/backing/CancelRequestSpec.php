<?php
namespace spec\groupcash\bank\backing;

use spec\groupcash\bank\scenario\Scenario;

/**
 * A request for coins can be cancelled by an issuer.
 */
class CancelRequestSpec extends Scenario {

    function before() {
        parent::before();
        $this->given->_HasAuthorized('foo', 'issuer');
        $this->given->_HasRequested('bart', 1, 'foo');
    }

    function notAuthorized() {
        $this->tryThat->_CancelsTheRequestOf_For('not issuer', 'bart', 'foo');
        $this->then->ItShouldFailWith('Not authorized for this currency.');
    }

    function noRequest() {
        $this->tryThat->_CancelsTheRequestOf_For('issuer', 'not bart', 'foo');
        $this->then->ItShouldFailWith('There is not active request for this account.');
    }

    function requestAlreadyCancelled() {
        $this->given->TheRequestBy_For_WasCancelled('bart', 'foo');
        $this->tryThat->_CancelsTheRequestOf_For('issuer', 'bart', 'foo');
        $this->then->ItShouldFailWith('There is not active request for this account.');
    }

    function requestAlreadyApproved() {
        $this->given->TheRequestBy_For_WasApprovedWithTheContributions('bart', 'foo', []);
        $this->tryThat->_CancelsTheRequestOf_For('issuer', 'bart', 'foo');
        $this->then->ItShouldFailWith('There is not active request for this account.');
    }

    function succeed() {
        $this->when->_CancelsTheRequestOf_For('issuer', 'bart', 'foo');
        $this->then->theRequestFrom_For_ShouldBeCancelled_By('bart', 'foo', 'issuer');
    }
}