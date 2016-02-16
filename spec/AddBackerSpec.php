<?php
namespace spec\groupcash\bank;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Backers give delivery promises that back coins of a currency. They are managed by the issuers of a currency.
 *
 * @property Scenario scenario <-
 */
class AddBackerSpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
    }

    function notAnIssuer() {
        $this->scenario->tryThat->_Adds_To('not issuer', 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function issuerOfOtherCurrency() {
        $this->scenario->tryThat->_Adds_To('issuer', 'backer', 'not foo');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function success() {
        $this->scenario->when->_Adds_To('issuer', 'backer', 'foo');
        $this->scenario->then->allShouldBeFine();
    }
}