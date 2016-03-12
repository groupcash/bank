<?php
namespace spec\groupcash\bank\basic;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Backers that are created by issuers of a currency are managed by the system
 *
 * @property Scenario scenario <-
 */
class CreateBackerSpec {

    function before() {
        $this->scenario->given->_HasAuthorized('foo', 'issuer');
    }

    function notAuthorized() {
        $this->scenario->tryThat->_CreatesANewBackerFor('not issuer', 'foo');
        $this->scenario->then->ItShouldFailWith('Not an authorized issuer for this currency.');
    }

    function succeed() {
        $this->scenario->when->_CreatesANewBackerFor('issuer', 'foo');
        $this->scenario->then->ANewBacker_ShouldBeCreatedFor_By('fake', 'foo', 'issuer');
    }
}