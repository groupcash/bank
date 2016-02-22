<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Backers give delivery promises that back coins of one or multiple currencies.
 *
 * @property Scenario scenario <-
 */
class CreateBackerSpec {

    function success() {
        $this->scenario->when->ICreateABacker('backer');
        $this->scenario->then->allShouldBeFine();
    }

    function nameAlreadyTaken() {
        $this->scenario->given->ICreateABacker_Named('backer', 'bart');
        $this->scenario->tryThat->ICreateABacker_Named('backer', 'bart');
        $this->scenario->then->itShouldFailWith('A backer with this name already exists.');
    }
}