<?php
namespace spec\groupcash\bank;
use spec\groupcash\bank\scenario\Scenario;

/**
 * An issuer that has added a backer to a currency can declare promises of this backer.
 *
 * @property Scenario scenario <-
 */
class AddBackerToCurrencySpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->_Authorizes('bar', 'other issuer');
        $this->scenario->given->ICreateABacker('backer');
    }

    function notAnIssuer() {
        $this->scenario->tryThat->_Adds_To('not issuer', 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function issuerOfOtherCurrency() {
        $this->scenario->tryThat->_Adds_To('issuer', 'backer', 'not foo');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function backerDoesNotExist() {
        $this->scenario->tryThat->_Adds_To('issuer', 'not backer', 'foo');
        $this->scenario->then->itShouldFailWith('This backer does not exist.');
    }

    function success() {
        $this->scenario->given->_Adds_To('other issuer', 'backer', 'bar');
        $this->scenario->then->allShouldBeFine();
    }

    function backerAlreadyAdded() {
        $this->scenario->given->_Adds_To('issuer', 'backer', 'foo');
        $this->scenario->tryThat->_Adds_To('issuer', 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('This backer was already added to this currency.');
    }

    function existingBackerAlreadyAdded() {
        $this->scenario->given->_Adds_To('other issuer', 'backer', 'bar');
        $this->scenario->tryThat->_Adds_To('other issuer', 'backer', 'bar');
        $this->scenario->then->itShouldFailWith('This backer was already added to this currency.');
    }
}