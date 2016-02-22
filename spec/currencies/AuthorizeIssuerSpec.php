<?php
namespace spec\groupcash\bank\currencies;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Issuers are authorized by the root of a currency.
 *
 * @property Scenario scenario <-
 */
class AuthorizeIssuerSpec {

    function success() {
        $this->scenario->when->_Authorizes('foo', 'issuer');
        $this->scenario->then->allShouldBeFine();
    }

    function alreadyAuthorized() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->tryThat->_Authorizes('foo', 'issuer');
        $this->scenario->then->itShouldFailWith('This issuer is already authorized for this currency.');
    }

    function mutlipleCurrencies() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->when->_Authorizes('bar', 'issuer');
        $this->scenario->then->allShouldBeFine();
    }
}