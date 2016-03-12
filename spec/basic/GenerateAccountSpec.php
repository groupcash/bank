<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Accounts have a private key and a public address, can send and receive coins.
 *
 * @property Scenario scenario <-
 */
class GenerateAccountSpec {

    function withoutPassword() {
        $this->scenario->when->IGenerateAnAccount();
        $this->scenario->then->ItShouldReturnANewAccountWithTheKey_AndTheAddress('fake key', 'fake');
    }

    function withPassword() {
        $this->scenario->when->ICreateAnAccountWithThePassword('foo');
        $this->scenario->then->ItShouldReturnANewAccountWithTheKey_AndTheAddress('fake key encrypted with foo', 'fake');
    }

    function emptyPassword() {
        $this->scenario->when->ICreateAnAccountWithThePassword('');
        $this->scenario->then->ItShouldReturnANewAccountWithTheKey_AndTheAddress('fake key', 'fake');
    }
}