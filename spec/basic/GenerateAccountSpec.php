<?php
namespace spec\groupcash\bank\basic;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Accounts have a private key and a public address, can send and receive coins.
 */
class GenerateAccountSpec extends Scenario {

    function withoutPassword() {
        $this->when->IGenerateAnAccount();
        $this->then->ItShouldReturnANewAccountWithTheKey_AndTheAddress('fake key', 'fake');
    }

    function withPassword() {
        $this->when->ICreateAnAccountWithThePassword('foo');
        $this->then->ItShouldReturnANewAccountWithTheKey_AndTheAddress('fake key encrypted with foo', 'fake');
    }

    function emptyPassword() {
        $this->when->ICreateAnAccountWithThePassword('');
        $this->then->ItShouldReturnANewAccountWithTheKey_AndTheAddress('fake key', 'fake');
    }
}