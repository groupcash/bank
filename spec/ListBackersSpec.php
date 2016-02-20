<?php
namespace spec\groupcash\bank;

use spec\groupcash\bank\scenario\Scenario;

/**
 * @property Scenario scenario <-
 */
class ListBackersSpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->_Authorizes('bar', 'issuer');
    }

    function noBackers() {
        $this->scenario->when->IListBackersOf('bar');
        $this->scenario->then->thereShouldBe_Backers(0);
    }

    function otherCurrency() {
        $this->scenario->given->_Adds_To_Named('issuer', 'backer', 'foo', 'The Backer');
        $this->scenario->when->IListBackersOf('bar');
        $this->scenario->then->thereShouldBe_Backers(0);
    }

    function oneBacker() {
        $this->scenario->given->_Adds_To_Named('issuer', 'backer', 'foo', 'The Backer');

        $this->scenario->when->IListBackersOf('foo');
        $this->scenario->then->thereShouldBe_Backers(1);

        $this->scenario->then->backer_shouldHaveTheCurrency(1, 'foo');
        $this->scenario->then->backer_shouldHaveTheName(1, 'The Backer');
        $this->scenario->then->backer_shouldHaveTheAddress(1, 'backer');
    }

    function sortByName() {
        $this->scenario->given->_Adds_To_Named('issuer', 'backer', 'foo', 'B');
        $this->scenario->given->_Adds_To_Named('issuer', 'backer', 'foo', 'A');
        $this->scenario->given->_Adds_To_Named('issuer', 'backer', 'foo', 'C');
        $this->scenario->when->IListBackersOf('foo');
        $this->scenario->then->thereShouldBe_Backers(3);
        $this->scenario->then->backer_shouldHaveTheName(1, 'A');
        $this->scenario->then->backer_shouldHaveTheName(2, 'B');
        $this->scenario->then->backer_shouldHaveTheName(3, 'C');
    }

    function sortByCurrency() {
        $this->scenario->given->IRegister_As('foo', 'a');
        $this->scenario->given->IRegister_As('bar', 'b');

        $this->scenario->given->_Adds_To_Named('issuer', 'backer', 'bar', 'B');
        $this->scenario->given->_Adds_To_Named('issuer', 'backer', 'foo', 'A');
        $this->scenario->given->_Adds_To_Named('issuer', 'backer', 'bar', 'A');

        $this->scenario->when->IListAllBackers();

        $this->scenario->then->thereShouldBe_Backers(3);
        $this->scenario->then->backer_shouldHaveTheCurrency(1, 'foo');
        $this->scenario->then->backer_shouldHaveTheCurrency(2, 'bar');
        $this->scenario->then->backer_shouldHaveTheCurrency(3, 'bar');
    }
}