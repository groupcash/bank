<?php
namespace spec\groupcash\bank\basic;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Issuers are accounts that are authorized by a currency to issue coins.
 *
 * @property Scenario scenario <-
 */
class AuthorizeIssuerSpec {

    function before() {
        $this->scenario->given->TheCurrency_WasEstablished('foo');
    }

    function notAnEstablishedCurrency() {
        $this->scenario->tryThat->_Authorizes('baz', 'bar');
        $this->scenario->then->ItShouldFailWith('Not an established currency.');
    }

    function success() {
        $this->scenario->when->_Authorizes('foo', 'bar');
        $this->scenario->then->TheIssuer_ShouldBeAuthorizedBy('bar', 'foo');
    }

    function issuerAlreadyAuthorized() {
        $this->scenario->given->_HasAuthorized('foo', 'bar');
        $this->scenario->tryThat->_Authorizes('foo', 'bar');
        $this->scenario->then->ItShouldFailWith('This issuer is already authorized.');
    }

    function authorizedByAnotherCurrency() {
        $this->scenario->given->TheCurrency_WasEstablished('baz');
        $this->scenario->given->_HasAuthorized('baz', 'bar');

        $this->scenario->when->_Authorizes('foo', 'bar');
        $this->scenario->then->TheIssuer_ShouldBeAuthorizedBy('bar', 'foo');
    }
}