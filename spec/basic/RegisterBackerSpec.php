<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * A backer is an account that coins can be issued to and that confirms transactions.
 *
 * @property Scenario scenario <-
 */
class RegisterBackerSpec {

    function succeed() {
        $this->scenario->when->IRegister_AsBackerWithTheName('backer', 'name of backer');
        $this->scenario->then->TheBacker_ShouldBeRegisteredUnder('backer', 'name of backer');
    }

    function emptyName() {
        $this->scenario->tryThat->IRegister_AsBackerWithTheName('backer', "\t ");
        $this->scenario->then->ItShouldFailWith('The name cannot be empty.');
    }

    function nameAlreadyTaken() {
        $this->scenario->given->ABackerWasRegisteredUnder('foo');
        $this->scenario->tryThat->IRegister_AsBackerWithTheName('backer', 'foo');
        $this->scenario->then->ItShouldFailWith('A backer with this name is already registered.');
    }

    function withDetails() {
        $this->scenario->when->IRegister_AsBackerWithTheName_AndTheDetails('backer', 'Foo', 'Some details');
        $this->scenario->tryThat->IRegister_AsBackerWithTheName('backer', 'Foo');
        $this->scenario->then->TheDetailsOfBacker_ShouldBeChangedTo('backer', 'Some details');
    }
}