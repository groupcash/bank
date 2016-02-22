<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Lists currencies with their names and addresses.
 *
 * @property Scenario scenario <-
 */
class ListCurrenciesSpec {

    function noCurrencies() {
        $this->scenario->when->IListAllCurrencies();
        $this->scenario->then->thereShouldBe_Currencies(0);
    }

    function oneCurrency() {
        $this->scenario->given->IRegister_As('foo', 'bar');
        $this->scenario->when->IListAllCurrencies();
        $this->scenario->then->thereShouldBe_Currencies(1);
        $this->scenario->then->currency_ShouldHaveTheAddress_AndTheName(1, 'foo', 'bar');
    }

    function sortByName() {
        $this->scenario->given->IRegister_As('foo', 'c');
        $this->scenario->given->IRegister_As('bar', 'a');
        $this->scenario->given->IRegister_As('baz', 'b');

        $this->scenario->when->IListAllCurrencies();
        $this->scenario->then->thereShouldBe_Currencies(3);
        $this->scenario->then->currency_ShouldHaveTheAddress_AndTheName(1, 'bar', 'a');
        $this->scenario->then->currency_ShouldHaveTheAddress_AndTheName(2, 'baz', 'b');
        $this->scenario->then->currency_ShouldHaveTheAddress_AndTheName(3, 'foo', 'c');
    }
}