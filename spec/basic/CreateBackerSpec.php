<?php
namespace spec\groupcash\bank\basic;
use spec\groupcash\bank\scenario\Scenario;

/**
 * A backer is an account that coins can be issued to and that confirms transactions.
 *
 * @property Scenario scenario <-
 */
class CreateBackerSpec {

    function succeed() {
        $this->scenario->when->ICreateANewBacker();
        $this->scenario->then->ANewBackerWithTheKey_ShouldBeCreated('fake key');
    }

    function withName() {
        $this->scenario->when->ICreateANewBackerWithTheName('foo');
        $this->scenario->then->ANewBackerWithTheKey_ShouldBeCreated('fake key');
        $this->scenario->then->TheBacker_ShouldBeRegisteredUnder('fake', 'foo');
    }

    function nameAlreadyTaken() {
        $this->scenario->given->ABackerWasRegisteredUnder('foo');
        $this->scenario->tryThat->ICreateANewBackerWithTheName('foo');
        $this->scenario->then->ItShouldFailWith('A backer with this name is already registered.');
    }

    function withDetails() {
        $this->scenario->when->ICreateANewBackerWithTheDetails('Some details');
        $this->scenario->then->TheDetailsOfBacker_ShouldBeChangedTo('fake', 'Some details');
    }
}