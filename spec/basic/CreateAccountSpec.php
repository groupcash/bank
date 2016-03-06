<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Accounts have a private key and a public address, can send and receive coins.
 *
 * @property Scenario scenario <-
 */
class CreateAccountSpec {

    function withoutPassword() {
        $this->scenario->when->ICreateAnAccount();
        $this->scenario->then->ItShouldReturnANewAccountWithTheKey_AndTheAddress('fake key', 'fake');
        $this->scenario->then->AnAccountWithTheAddress_ShouldBeCreated('fake');
    }

    function withPassword() {
    }
}