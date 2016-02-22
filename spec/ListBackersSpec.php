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
        $this->scenario->when->IListAllBackers();
        $this->scenario->then->thereShouldBe_Backers(0);
    }

    function oneBacker() {
        $this->scenario->given->ICreateABacker_Named('backer', 'The Backer');

        $this->scenario->when->IListAllBackers();
        $this->scenario->then->thereShouldBe_Backers(1);

        $this->scenario->then->backer_shouldHaveTheCurrencies(1, []);
        $this->scenario->then->backer_shouldHaveTheName(1, 'The Backer');
        $this->scenario->then->backer_shouldHaveTheAddress(1, 'backer');
    }

    function sortByName() {
        $this->scenario->given->ICreateABacker_Named('backer foo', 'B');
        $this->scenario->given->ICreateABacker_Named('backer bar', 'A');
        $this->scenario->given->ICreateABacker_Named('backer baz', 'C');

        $this->scenario->when->IListAllBackers();

        $this->scenario->then->thereShouldBe_Backers(3);
        $this->scenario->then->backer_shouldHaveTheName(1, 'A');
        $this->scenario->then->backer_shouldHaveTheName(2, 'B');
        $this->scenario->then->backer_shouldHaveTheName(3, 'C');
    }

    function oneCurrency() {
        $this->scenario->given->ICreateABacker_Named('backer', 'The Backer');
        $this->scenario->given->_Adds_To('issuer', 'backer', 'foo');

        $this->scenario->when->IListAllBackers();
        $this->scenario->then->thereShouldBe_Backers(1);

        $this->scenario->then->backer_shouldHaveTheCurrencies(1, ['foo']);
        $this->scenario->then->backer_shouldHaveTheName(1, 'The Backer');
        $this->scenario->then->backer_shouldHaveTheAddress(1, 'backer');
    }

    function multipleCurrencies() {
        $this->scenario->given->ICreateABacker('backer');
        $this->scenario->given->_Adds_To('issuer', 'backer', 'foo');
        $this->scenario->given->_Adds_To('issuer', 'backer', 'bar');

        $this->scenario->when->IListAllBackers();

        $this->scenario->then->thereShouldBe_Backers(1);
        $this->scenario->then->backer_shouldHaveTheCurrencies(1, ['foo', 'bar']);
    }
}