<?php
namespace spec\groupcash\bank\trading;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Deposited coins are confirmed by their backers to avoid double-spending.
 *
 * @property Scenario scenario <-
 */
class ConfirmCoinsSpec {

    function before() {
        $this->scenario->blockedBy('Confirmation');

        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->ICreateABacker('backer');
        $this->scenario->given->_Adds_To('issuer', 'backer', 'foo');
        $this->scenario->given->_Declares_Of_By_For('issuer', 1, 'Promise', 'backer', 'foo');
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'backer');
        $this->scenario->given->_Sends__To('backer', 1, 'foo', 'bart');
    }

    function withdrawnCoinsAreConfirmed() {
    }

    function notABacker() {
        $this->scenario->tryThat->IDeposit_To([
            // Coin backed by another backer
        ], 'not bart');
        $this->scenario->then->itShouldFailWith('This backer was not added to this currency.');
    }

    function notAnIssuer() {
        $this->scenario->tryThat->IDeposit_To([
            // Coin issued by another issuer
        ], 'bart');
        $this->scenario->then->itShouldFailWith('The issuer is not authorized.');
    }

    function inconsistentCoin() {
        $this->scenario->tryThat->IDeposit_To([
            // Coin with broken transference chain
        ], 'lisa');
        $this->scenario->then->itShouldFailWith('Signed by non-owner [not bart].');
    }

    function alreadyTransferred() {
        $this->scenario->blockedBy('proper accounting');

        $this->scenario->given->_WithdrawsOne_As('bart', 'foo', 'of bart');
        $this->scenario->given->_TransfersCoin_To_As('bart', 'of bart', 'homer', 'of homer');
        $this->scenario->given->_TransfersCoin_To_As('bart', 'of bart', 'marge', 'of marge');

        $this->scenario->given->IDepositCoin_To('of homer', 'homer');
        $this->scenario->tryThat->IDepositCoin_To('of marge', 'marge');
        $this->scenario->then->itShouldFailWith('Could not validate coin 1: This coin was already transferred.');
    }

    function transferredAfterDeposited() {
        $this->scenario->blockedBy('proper accounting');

        $this->scenario->given->_TransfersCoin_To_As('bart', 'issued', 'lisa', 'of lisa');
        $this->scenario->given->_TransfersCoin_To_As('lisa', 'of lisa', 'homer', 'of homer');

        $this->scenario->given->IDepositCoin_To('of homer', 'homer');

        $this->scenario->given->_TransfersCoin_To_As('homer', 'of homer', 'marge', 'of marge');
        $this->scenario->tryThat->IDepositCoin_To('of marge', 'marge');

        $this->scenario->then->itShouldFailWith('Could not validate coin 1: This coin was already transferred.');
    }

    function transferWithdrawnCoin() {
        $this->scenario->blockedBy('proper accounting');

        $this->scenario->given->_TransfersCoin_To_As('bart', 'issued', 'lisa', 'of lisa');
        $this->scenario->given->_TransfersCoin_To_As('lisa', 'of lisa', 'homer', 'of homer');

        $this->scenario->given->IDepositCoin_To('of homer', 'homer');

        $this->scenario->given->_WithdrawsOne_As('homer', 'foo', 'withdrawn by homer');
        $this->scenario->given->_TransfersCoin_To_As('homer', 'withdrawn by homer', 'marge', 'of marge');
        $this->scenario->when->IDepositCoin_To('of marge', 'marge');

        $this->scenario->then->allShouldBeFine();
    }

    function transferFractions() {
        $this->scenario->blockedBy('proper accounting');

        $this->scenario->given->_Transfers_OfCoin_To_As('bart', .5, 'issued', 'lisa', 'of lisa');

        $this->scenario->given->IDepositCoin_To('of lisa', 'lisa');
        $this->scenario->given->IDepositCoin_To('of lisa', 'lisa');
        $this->scenario->tryThat->IDepositCoin_To('of lisa', 'lisa');

        $this->scenario->then->itShouldFailWith('Could not validate coin 1: This coin was already transferred.');
    }
}