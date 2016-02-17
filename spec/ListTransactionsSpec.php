<?php
namespace spec\groupcash\bank;

use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\projecting\Transaction;
use groupcash\php\model\Fraction;
use spec\groupcash\bank\scenario\Scenario;

/**
 * List all transactions of one account. All sent and all received coins.
 *
 * @property Scenario scenario <-
 */
class ListTransactionsSpec {

    function before() {
        $this->scenario->given->nowIs('2010-11-12 13:14:15');
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->_Adds_To('issuer', 'bart', 'foo');
        $this->scenario->given->_Declares_Of_By_For('issuer', 10, 'My Promise', 'bart', 'foo');
        $this->scenario->given->_issues__to('issuer', 10, 'foo', 'bart');
    }

    function noTransactions() {
        $this->scenario->when->_ListsTheirTransactions('lisa');
        $this->scenario->then->thereShouldBe_Transactions(0);
    }

    function oneReceived() {
        $this->scenario->given->nowIs('2011-12-13 14:15:16');
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'lisa');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->thereShouldBe_Transactions(1);
        $this->scenario->then->transaction_ShouldBe(1, new Transaction(
            new \DateTimeImmutable('2011-12-13 14:15:16'),
            new CurrencyIdentifier('foo'),
            new Fraction(1)
        ));
    }

    function oneReceivedAndSent() {
        $this->scenario->given->nowIs('2011-12-13 14:15:16');
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('lisa', 1, 'foo', 'homer');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->thereShouldBe_Transactions(2);
        $this->scenario->then->transaction_ShouldBe(2, new Transaction(
            new \DateTimeImmutable('2011-12-13 14:15:16'),
            new CurrencyIdentifier('foo'),
            new Fraction(-1)
        ));
    }

    function issuedCoins() {
        $this->scenario->given->_Adds_To('issuer', 'apu', 'foo');
        $this->scenario->given->_Declares_Of_By_For('issuer', 10, 'My Promise', 'apu', 'foo');

        $this->scenario->given->nowIs('2010-11-12 13:14:15');
        $this->scenario->given->_issues__to('issuer', 10, 'foo', 'apu');

        $this->scenario->when->_ListsTheirTransactions('apu');

        $this->scenario->then->thereShouldBe_Transactions(1);
        $this->scenario->then->transaction_ShouldBe(1, new Transaction(
            new \DateTimeImmutable('2010-11-12 13:14:15'),
            new CurrencyIdentifier('foo'),
            new Fraction(10)
        ));
    }

    function multiple() {
        $this->scenario->given->nowIs('2011-12-13 14:15:16');
        $this->scenario->given->_Sends__To('bart', 7, 'foo', 'lisa');

        $this->scenario->given->_Sends__To('lisa', 3, 'foo', 'homer');
        $this->scenario->given->_Sends__th_To('lisa', 5, 2, 'foo', 'marge');

        $this->scenario->given->nowIs('2011-12-13 15:16:17');
        $this->scenario->given->_Sends__To('homer', 2, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', 2, 'foo', 'lisa');
        $this->scenario->given->_Sends__th_To('marge', 2, 5, 'foo', 'lisa');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->thereShouldBe_Transactions(6);
        $this->scenario->then->transaction_ShouldBe(1, new Transaction(
            new \DateTimeImmutable('2011-12-13 14:15:16'),
            new CurrencyIdentifier('foo'),
            new Fraction(7)
        ));
        $this->scenario->then->transaction_ShouldBe(2, new Transaction(
            new \DateTimeImmutable('2011-12-13 14:15:16'),
            new CurrencyIdentifier('foo'),
            new Fraction(-3)
        ));
        $this->scenario->then->transaction_ShouldBe(3, new Transaction(
            new \DateTimeImmutable('2011-12-13 14:15:16'),
            new CurrencyIdentifier('foo'),
            new Fraction(-5, 2)
        ));
        $this->scenario->then->transaction_ShouldBe(4, new Transaction(
            new \DateTimeImmutable('2011-12-13 15:16:17'),
            new CurrencyIdentifier('foo'),
            new Fraction(2)
        ));
    }

    function calculateTotal() {
        $this->scenario->given->_Sends__To('bart', 7, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('lisa', 3, 'foo', 'homer');
        $this->scenario->given->_Sends__th_To('lisa', 5, 2, 'foo', 'marge');
        $this->scenario->given->_Sends__To('homer', 2, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', 2, 'foo', 'lisa');
        $this->scenario->given->_Sends__th_To('marge', 2, 5, 'foo', 'lisa');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->theTotalShouldBe__th(59, 10);
    }
}