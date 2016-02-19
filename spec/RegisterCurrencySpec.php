<?php
namespace spec\groupcash\bank;

use spec\groupcash\bank\scenario\Scenario;

/**
 * The address of a currency can be registered with a name.
 *
 * @property Scenario scenario <-
 */
class RegisterCurrencySpec {

    function emptyName() {
        $this->scenario->tryThat->IRegister_As('public key', " \t");
        $this->scenario->then->itShouldFailWith('The currency name cannot be empty.');
    }

    function success() {
        $this->scenario->when->IRegister_As('public key', 'foo');
        $this->scenario->then->allShouldBeFine();
    }

    function duplicateName() {
        $this->scenario->given->IRegister_As('public key', 'foo');
        $this->scenario->tryThat->IRegister_As('other key', 'foo');
        $this->scenario->then->itShouldFailWith('A currency with this name is already registered.');
    }
}