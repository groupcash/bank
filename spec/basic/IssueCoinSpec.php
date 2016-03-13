<?php
namespace spec\groupcash\bank\basic;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins are created by issuing them to backers.
 */
class IssueCoinSpec extends Scenario {

    function emptyDescription() {
        $this->tryThat->_Issues__To_BackedBy('issuer', 1, 'coin', 'backer', "\t ");
        $this->then->ItShouldFailWith('The description cannot be empty.');
    }

    function notAuthorized() {
        $this->tryThat->_Issues__To_BackedBy('not issuer', 1, 'coin', 'backer', 'Foo');
        $this->then->ItShouldFailWith('Not authorized to issue this currency.');
    }

    function succeed() {
        $this->given->_HasAuthorized('coin', 'issuer');
        $this->when->_Issues__To_BackedBy('issuer', 1, 'coin', 'backer', 'Foo');
        $this->then->ACoinWorth__BackedBy_ShouldBeIssuedTo_SignedBy(1, 'coin', 'Foo', 'backer', 'issuer');
        $this->then->_ShouldReceiveACoinWorth__WithTheSubject('backer', 1, 'coin', 'Issued');
    }
}