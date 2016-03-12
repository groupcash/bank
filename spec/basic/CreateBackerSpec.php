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
        $this->scenario->then->ANewBacker_ShouldBeCreated('fake');
    }

    function emptyName() {
        $this->scenario->tryThat->ICreateANewBackerWithTheName("\t ");
        $this->scenario->then->ItShouldFailWith('The name cannot be empty.');
    }

    function withName() {
        $this->scenario->when->ICreateANewBackerWithTheName('foo ');
        $this->scenario->then->ANewBacker_ShouldBeCreated('fake');
        $this->scenario->then->TheBacker_ShouldBeRegisteredUnder('fake', 'foo');
    }

    function nameAlreadyTaken() {
        $this->scenario->given->ABackerWasRegisteredUnder('foo');
        $this->scenario->tryThat->ICreateANewBackerWithTheName('foo');
        $this->scenario->then->ItShouldFailWith('A backer with this name is already registered.');
    }

    function withDetails() {
        $this->scenario->when->ICreateANewBackerWithTheName_AndTheDetails('Foo', 'Some details');
        $this->scenario->then->TheDetailsOfBacker_ShouldBeChangedTo('fake', 'Some details');
    }

    function withOnlyDetails() {
        $this->scenario->tryThat->ICreateANewBackerWithTheName_AndTheDetails(null, 'Some details');
        $this->scenario->then->ItShouldFailWith('A backer needs a name to be registered with details.');
    }
}