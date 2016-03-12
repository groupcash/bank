<?php
namespace spec\groupcash\bank\backing;

use spec\groupcash\bank\scenario\Scenario;

/**
 * A request for coins can be cancelled by an issuer.
 *
 * @property Scenario scenario <-
 */
class CancelRequestSpec {

    function before() {
        $this->scenario->given->_HasAuthorized('foo', 'issuer');
        $this->scenario->given->_HasRequested('bart', 1, 'foo');
    }

    function notAuthorized() {
        $this->scenario->tryThat->_CancelsTheRequestOf_For('not issuer', 'bart', 'foo');
        $this->scenario->then->ItShouldFailWith('Not authorized for this currency.');
    }

    function noRequest() {
        $this->scenario->tryThat->_CancelsTheRequestOf_For('issuer', 'not bart', 'foo');
        $this->scenario->then->ItShouldFailWith('There is not active request for this account.');
    }

    function requestAlreadyCancelled() {
        $this->scenario->given->TheRequestBy_For_WasCancelled('bart', 'foo');
        $this->scenario->tryThat->_CancelsTheRequestOf_For('issuer', 'bart', 'foo');
        $this->scenario->then->ItShouldFailWith('There is not active request for this account.');
    }

    function requestAlreadyApproved() {
        $this->scenario->given->TheRequestBy_For_WasApproved('bart', 'foo');
        $this->scenario->tryThat->_CancelsTheRequestOf_For('issuer', 'bart', 'foo');
        $this->scenario->then->ItShouldFailWith('There is not active request for this account.');
    }

    function succeed() {
        $this->scenario->when->_CancelsTheRequestOf_For('issuer', 'bart', 'foo');
        $this->scenario->then->theRequestFrom_For_ShouldBeCancelled_By('bart', 'foo', 'issuer');
    }
}