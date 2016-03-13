<?php
namespace spec\groupcash\bank\backing;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Backers that are created by issuers of a currency are managed by the system
 */
class CreateBackerSpec extends Scenario {

    function before() {
        parent::before();
        $this->given->_HasAuthorized('foo', 'issuer');
    }

    function notAuthorized() {
        $this->tryThat->_CreatesANewBackerFor('not issuer', 'foo');
        $this->then->ItShouldFailWith('Not an authorized issuer for this currency.');
    }

    function succeed() {
        $this->when->_CreatesANewBackerFor('issuer', 'foo');
        $this->then->ANewBacker_ShouldBeCreatedFor_By('fake', 'foo', 'issuer');
    }
}