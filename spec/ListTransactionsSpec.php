<?php
namespace spec\groupcash\bank;

use spec\groupcash\bank\scenario\Scenario;

/**
 * List all transactions of one account. All sent and all received coins.
 *
 * @property Scenario scenario <-
 */
class ListTransactionsSpec {

    function before() {
        $this->scenario->given->nowIs('2010-11-12');
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
        $this->scenario->given->nowIs('2011-12-13');
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'lisa');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->thereShouldBe_Transactions(1);
        $this->scenario->then->transaction_ShouldBeOf__On(1, 1, 'foo', '2011-12-13');
    }

    function oneReceivedAndSent() {
        $this->scenario->given->nowIs('2011-12-13');
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('lisa', 1, 'foo', 'homer');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->thereShouldBe_Transactions(2);
        $this->scenario->then->transaction_ShouldBeOf__On(2, -1, 'foo', '2011-12-13');
    }

    function withSubject() {
        $this->scenario->given->nowIs('2011-12-13');
        $this->scenario->given->_Sends__To_WithSubject('bart', 1, 'foo', 'lisa', 'Foo!');
        $this->scenario->given->_Sends__To_WithSubject('lisa', 1, 'foo', 'homer', 'Bar!');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->thereShouldBe_Transactions(2);
        $this->scenario->then->transaction_ShouldHaveTheSubject(1, 'Foo!');
        $this->scenario->then->transaction_ShouldHaveTheSubject(2, 'Bar!');
    }

    function issuedCoins() {
        $this->scenario->given->_Adds_To('issuer', 'apu', 'foo');
        $this->scenario->given->_Declares_Of_By_For('issuer', 10, 'My Promise', 'apu', 'foo');

        $this->scenario->given->nowIs('2010-11-12');
        $this->scenario->given->_issues__to('issuer', 10, 'foo', 'apu');

        $this->scenario->when->_ListsTheirTransactions('apu');

        $this->scenario->then->thereShouldBe_Transactions(1);
        $this->scenario->then->transaction_ShouldHaveTheSubject(1, 'Issued');
    }

    function multiple() {
        $this->scenario->given->nowIs('2011-12-13');
        $this->scenario->given->_Sends__To('bart', 7, 'foo', 'lisa');

        $this->scenario->given->_Sends__To('lisa', 3, 'foo', 'homer');
        $this->scenario->given->_Sends__To('lisa', 2.5, 'foo', 'marge');

        $this->scenario->given->nowIs('2012-11-10');
        $this->scenario->given->_Sends__To('homer', 2, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', 2, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('marge', 0.4, 'foo', 'lisa');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->thereShouldBe_Transactions(6);
        $this->scenario->then->transaction_ShouldBeOf__On(1, 7, 'foo', '2011-12-13');
        $this->scenario->then->transaction_ShouldBeOf__On(2, -3, 'foo', '2011-12-13');
        $this->scenario->then->transaction_ShouldBeOf__On(3, -2.5, 'foo', '2011-12-13');
        $this->scenario->then->transaction_ShouldBeOf__On(4, 2, 'foo', '2012-11-10');
    }

    function calculateTotal() {
        $this->scenario->given->_Sends__To('bart', 7, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('lisa', 3, 'foo', 'homer');
        $this->scenario->given->_Sends__To('lisa', 2.5, 'foo', 'marge');
        $this->scenario->given->_Sends__To('homer', 2, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('bart', 2, 'foo', 'lisa');
        $this->scenario->given->_Sends__To('marge', 0.4, 'foo', 'lisa');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->theTotalShouldBe(5.9);
    }

    function useNameOfCurrency() {
        $this->scenario->given->_Sends__To('bart', 1, 'foo', 'lisa');
        $this->scenario->given->IRegister_As('foo', 'name of foo');

        $this->scenario->when->_ListsTheirTransactions('lisa');

        $this->scenario->then->thereShouldBe_Transactions(1);
        $this->scenario->then->transaction_ShouldHaveTheCurrencyName(1, 'name of foo');
    }
}