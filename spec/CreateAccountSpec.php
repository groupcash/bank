<?php
namespace spec\groupcash\bank;

use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use rtens\scrut\Assert;
use spec\groupcash\bank\fakes\FakeCryptography;
use spec\groupcash\bank\fakes\FakeRandomNumberGenerator;
use spec\groupcash\bank\fakes\FakeVault;
use spec\groupcash\bank\scenario\Scenario;

/**
 * An account is created by generating and encrypting a private key.
 *
 * @property Scenario scenario <-
 * @property Assert assert <-
 */
class CreateAccountSpec {

    function withoutPassword() {
        $this->scenario->when->ICreateAnAccountWithPassword(null);
        $this->scenario->then->isShouldReturn([
            'key' => 'private key',
            'address' => 'key'
        ]);
    }

    function withPassword() {
        $this->scenario->when->ICreateAnAccountWithPassword('password');
        $this->scenario->then->isShouldReturn([
            'key' => 'private key encrypted with secretpassword',
            'address' => 'key'
        ]);
    }

    function authenticate() {
        $auth = new Authenticator(new FakeCryptography(), new FakeVault(new FakeRandomNumberGenerator('secret ')));

        $this->assert->equals($auth->getKey(new Authentication('key')), 'key');
        $this->assert->equals($auth->getKey(new Authentication('key encrypted with secret password', 'password')), 'key');
    }
}