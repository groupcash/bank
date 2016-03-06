<?php
namespace spec\groupcash\bank\basic;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Currencies are established by signing its rules. It can be given a name for easier selection.
 *
 * @property Scenario scenario <-
 */
class EstablishCurrencySpec {

    function emptyRules() {
        $this->scenario->tryThat->_EstablishesACurrencyWithTheRules('foo key', "\t ");
        $this->scenario->then->ItShouldFailWith('The rules cannot be empty.');
    }

    function withoutName() {
        $this->scenario->when->_EstablishesACurrencyWithTheRules('foo key', 'Foo!');
        $this->scenario->then->ACurrency_WithTheRules_SignedBy_ShouldBeEstablished('foo', 'Foo!', 'foo key');
        $this->scenario->then->NoCurrencyShouldBeRegistered();
    }

    function currencyAlreadyEstablished() {
        $this->scenario->given->TheCurrency_WasEstablished('foo');
        $this->scenario->tryThat->_EstablishesACurrencyWithTheRules('foo key', 'Foo!');
        $this->scenario->then->ItShouldFailWith('This currency is already established.');
    }

    function withName() {
        $this->scenario->when->_RegistersTheCurrencyUnderTheName('foo key', 'bar');
        $this->scenario->then->TheCurrency_ShouldBeRegisteredAs('foo', 'bar');
    }

    function emptyName() {
        $this->scenario->tryThat->_RegistersTheCurrencyUnderTheName('foo key', "\t  ");
        $this->scenario->then->ItShouldFailWith('The name cannot be empty.');
    }

    function nameAlreadyTaken() {
        $this->scenario->given->ACurrencyWasRegisteredUnder('bar');
        $this->scenario->tryThat->_RegistersTheCurrencyUnderTheName('foo key', 'bar');
        $this->scenario->then->ItShouldFailWith('A currency is already registered under this name.');
    }
}