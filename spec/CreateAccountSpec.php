<?php
namespace spec\groupcash\bank;

use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CreatedAccount;
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
        $this->scenario->then->itShouldReturn(new CreatedAccount('key', 'private key'));
    }

    function withPassword() {
        $this->scenario->when->ICreateAnAccountWithPassword('password');
        $this->scenario->then->itShouldReturn(new CreatedAccount('key', 'private key encrypted with secretpassword'));
    }

    function authenticate() {
        $auth = new Authenticator(new FakeCryptography(), new FakeVault(new FakeRandomNumberGenerator('secret ')));

        $this->assert->equals($auth->getKey(new Authentication('key')), 'key');
        $this->assert->equals($auth->getKey(new Authentication('key encrypted with secret password', 'password')), 'key');
    }
}