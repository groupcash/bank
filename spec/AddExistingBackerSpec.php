<?php
namespace spec\groupcash\bank;
use spec\groupcash\bank\scenario\Scenario;

/**
 * A backer that was already added to one currency can also be added to another currency.
 *
 * @property Scenario scenario <-
 */
class AddExistingBackerSpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->_Authorizes('bar', 'other issuer');
        $this->scenario->given->_Adds_To('issuer', 'backer', 'foo');
    }

    function notAnIssuer() {
        $this->scenario->tryThat->_AddsExisting_To('not issuer', 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function issuerOfOtherCurrency() {
        $this->scenario->tryThat->_AddsExisting_To('issuer', 'backer', 'not foo');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function backerDoesNotExist() {
        $this->scenario->tryThat->_AddsExisting_To('issuer', 'not backer', 'foo');
        $this->scenario->then->itShouldFailWith('This backer does not exist.');
    }

    function success() {
        $this->scenario->given->_AddsExisting_To('other issuer', 'backer', 'bar');
        $this->scenario->then->allShouldBeFine();
    }

    function backerAlreadyAdded() {
        $this->scenario->tryThat->_AddsExisting_To('issuer', 'backer', 'foo');
        $this->scenario->then->itShouldFailWith('This backer was already added to this currency.');
    }

    function existingBackerAlreadyAdded() {
        $this->scenario->given->_AddsExisting_To('other issuer', 'backer', 'bar');
        $this->scenario->tryThat->_AddsExisting_To('other issuer', 'backer', 'bar');
        $this->scenario->then->itShouldFailWith('This backer was already added to this currency.');
    }
}