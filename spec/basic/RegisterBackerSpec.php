<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * A backer is an account that coins can be issued to and that confirms transactions.
 */
class RegisterBackerSpec extends Scenario {

    function succeed() {
        $this->when->IRegister_AsBackerWithTheName('backer', 'name of backer');
        $this->then->TheBacker_ShouldBeRegisteredUnder('backer', 'name of backer');
    }

    function emptyName() {
        $this->tryThat->IRegister_AsBackerWithTheName('backer', "\t ");
        $this->then->ItShouldFailWith('The name cannot be empty.');
    }

    function nameAlreadyTaken() {
        $this->given->ABackerWasRegisteredUnder('foo');
        $this->tryThat->IRegister_AsBackerWithTheName('backer', 'foo');
        $this->then->ItShouldFailWith('A backer with this name is already registered.');
    }

    function withDetails() {
        $this->when->IRegister_AsBackerWithTheName_AndTheDetails('backer', 'Foo', 'Some details');
        $this->tryThat->IRegister_AsBackerWithTheName('backer', 'Foo');
        $this->then->TheDetailsOfBacker_ShouldBeChangedTo('backer', 'Some details');
    }
}