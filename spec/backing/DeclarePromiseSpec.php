<?php
namespace spec\groupcash\bank\backing;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins are backed by delivery promises made by backers. The number of coins issuable per promise is limited.
 *
 * @property Scenario scenario <-
 */
class DeclarePromiseSpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->ICreateABacker('backer');
        $this->scenario->given->_Adds_To('issuer', 'backer', 'foo');
    }

    function notABacker() {
        $this->scenario->tryThat->_Declares_Of_By_For('issuer', 1, 'My Promise', 'not backer', 'foo');
        $this->scenario->then->itShouldFailWith('This backer was not added to this currency.');
    }

    function backerOfOtherCurrency() {
        $this->scenario->given->_Authorizes('not foo', 'issuer');

        $this->scenario->tryThat->_Declares_Of_By_For('issuer', 1, 'My Promise', 'backer', 'not foo');
        $this->scenario->then->itShouldFailWith('This backer was not added to this currency.');
    }

    function notAnIssuer() {
        $this->scenario->tryThat->_Declares_Of_By_For('not issuer', 1, 'My Promise', 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function issuerOfOtherCurrency() {
        $this->scenario->given->_Authorizes('not foo', 'not issuer');
        $this->scenario->given->_Adds_To('not issuer', 'backer', 'not foo');
        $this->scenario->tryThat->_Declares_Of_By_For('issuer', 1, 'My Promise', 'backer', 'not foo');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function success() {
        $this->scenario->when->_Declares_Of_By_For('issuer', 1, 'My Promise', 'backer', 'foo');
        $this->scenario->then->allShouldBeFine();
    }

    function emptyPromise() {
        $this->scenario->tryThat->_Declares_Of_By_For('issuer', 1, "  \t\n", 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('The promise cannot be empty.');
    }

    function negativeLimit() {
        $this->scenario->tryThat->_Declares_Of_By_For('issuer', -1, 'My Promise', 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('The limit must be positive.');
    }

    function zeroLimit() {
        $this->scenario->tryThat->_Declares_Of_By_For('issuer', 0, 'My Promise', 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('The limit must be positive.');
    }
}