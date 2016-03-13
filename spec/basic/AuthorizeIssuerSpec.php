<?php
namespace spec\groupcash\bank\basic;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Issuers are accounts that are authorized by a currency to issue coins.
 */
class AuthorizeIssuerSpec extends Scenario {

    function before() {
        parent::before();
        $this->given->TheCurrency_WasEstablished('foo');
    }

    function notAnEstablishedCurrency() {
        $this->tryThat->_Authorizes('baz', 'bar');
        $this->then->ItShouldFailWith('Not an established currency.');
    }

    function success() {
        $this->when->_Authorizes('foo', 'bar');
        $this->then->TheIssuer_ShouldBeAuthorizedBy('bar', 'foo');
    }

    function issuerAlreadyAuthorized() {
        $this->given->_HasAuthorized('foo', 'bar');
        $this->tryThat->_Authorizes('foo', 'bar');
        $this->then->ItShouldFailWith('This issuer is already authorized.');
    }

    function authorizedByAnotherCurrency() {
        $this->given->TheCurrency_WasEstablished('baz');
        $this->given->_HasAuthorized('baz', 'bar');

        $this->when->_Authorizes('foo', 'bar');
        $this->then->TheIssuer_ShouldBeAuthorizedBy('bar', 'foo');
    }
}