<?php
namespace spec\groupcash\bank\basic;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins are created by issuing them to backers.
 *
 * @property Scenario scenario <-
 */
class IssueCoinSpec {

    function emptyDescription() {
        $this->scenario->tryThat->_Issues__To_BackedBy('issuer', 1, 'coin', 'backer', "\t ");
        $this->scenario->then->ItShouldFailWith('The description cannot be empty.');
    }

    function notAuthorized() {
        $this->scenario->tryThat->_Issues__To_BackedBy('not issuer', 1, 'coin', 'backer', 'Foo');
        $this->scenario->then->ItShouldFailWith('Not authorized to issue this currency.');
    }

    function succeed() {
        $this->scenario->given->_HasAuthorized('coin', 'issuer');
        $this->scenario->when->_Issues__To_BackedBy('issuer', 1, 'coin', 'backer', 'Foo');
        $this->scenario->then->ACoinWorth__BackedBy_ShouldBeIssuedTo_SignedBy(1, 'coin', 'Foo', 'backer', 'issuer');
        $this->scenario->then->_ShouldReceive('backer', 1, 'coin');
    }
}