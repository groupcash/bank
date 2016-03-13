<?php
namespace spec\groupcash\bank\basic;
use spec\groupcash\bank\scenario\Scenario;

/**
 * Currencies are established by signing its rules. It can be given a name for easier selection.
 */
class EstablishCurrencySpec extends Scenario {

    function emptyRules() {
        $this->tryThat->_EstablishesACurrencyWithTheRules('foo', "\t ");
        $this->then->ItShouldFailWith('The rules cannot be empty.');
    }

    function withoutName() {
        $this->when->_EstablishesACurrencyWithTheRules('foo', 'Foo!');
        $this->then->ACurrency_WithTheRules_ShouldBeEstablished('foo', 'Foo!');
        $this->then->NoCurrencyShouldBeRegistered();
    }

    function currencyAlreadyEstablished() {
        $this->given->TheCurrency_WasEstablished('foo');
        $this->tryThat->_EstablishesACurrencyWithTheRules('foo', 'Foo!');
        $this->then->ItShouldFailWith('This currency is already established.');
    }

    function withName() {
        $this->when->_RegistersTheCurrencyUnderTheName('foo', 'bar');
        $this->then->TheCurrency_ShouldBeRegisteredAs('foo', 'bar');
    }

    function emptyName() {
        $this->tryThat->_RegistersTheCurrencyUnderTheName('foo', "\t  ");
        $this->then->ItShouldFailWith('The name cannot be empty.');
    }

    function nameAlreadyTaken() {
        $this->given->ACurrencyWasRegisteredUnder('bar');
        $this->tryThat->_RegistersTheCurrencyUnderTheName('foo', 'bar');
        $this->then->ItShouldFailWith('A currency is already registered under this name.');
    }
}