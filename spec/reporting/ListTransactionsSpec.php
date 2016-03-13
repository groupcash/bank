<?php
namespace spec\groupcash\bank\reporting;

use spec\groupcash\bank\scenario\Scenario;

/**
 * An account can list their transactions history with subjects
 */
class ListTransactionsSpec extends Scenario {

    function noTransactions() {
        $this->when->_ListsTheirTransactions('bart');
        $this->then->ThereShouldBeNoTransactions();
    }

    function receivedCoins() {
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 1, 'foo');
        $this->when->_ListsTheirTransactions('bart');
        $this->then->ThereShouldBe_Transactions(1);
        $this->then->Transaction_ShouldBeOf(1, 1, 'foo');
    }

    function receivedWithSubject() {
        $this->given->_HasReceivedACoin_WorthWithTheSubject('bart', 'one', 1, 'foo', 'Bar');
        $this->when->_ListsTheirTransactions('bart');
        $this->then->ThereShouldBe_Transactions(1);
        $this->then->Transaction_ShouldHaveTheSubject(1, 'Bar');
    }

    function sentCoins() {
        $this->given->_HasSentACoin_Worth__To('bart', 'one', 1, 'foo', 'lisa');
        $this->when->_ListsTheirTransactions('bart');
        $this->then->ThereShouldBe_Transactions(1);
        $this->then->Transaction_ShouldBeOf(1, -1, 'foo');
    }

    function sentWithSubject() {
        $this->given->_HasSentACoin_Worth__To_WithSubject('bart', 'one', 1, 'foo', 'lisa', 'Bar');
        $this->when->_ListsTheirTransactions('bart');
        $this->then->ThereShouldBe_Transactions(1);
        $this->then->Transaction_ShouldHaveTheSubject(1, 'Bar');
    }

    function severalTransactions() {
        $this->given->_HasSentACoin_Worth__To('bart', 'one', 3, 'foo', 'lisa');
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 5, 'foo');
        $this->given->_HasSentACoin_Worth__To('bart', 'one', 2, 'foo', 'lisa');
        $this->given->_HasSentACoin_Worth__To('bart', 'one', 1, 'foo', 'lisa');
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 3, 'foo');

        $this->when->_ListsTheirTransactions('bart');
        $this->then->ThereShouldBe_Transactions(5);
        $this->then->TheTransactionTotalIn_ShouldBe('foo', 2);
    }

    function mutlipleCurrencies() {
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 1, 'foo');
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 1, 'bar');
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 1, 'bar');
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 3, 'baz');

        $this->when->_ListsTheirTransactions('bart');
        $this->then->TheTransactionTotalIn_ShouldBe('foo', 1);
        $this->then->TheTransactionTotalIn_ShouldBe('bar', 2);
        $this->then->TheTransactionTotalIn_ShouldBe('baz', 3);
    }

    function sortByDate() {
        $this->given->NowIs('2011-12-12');
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 2, 'foo');
        $this->given->NowIs('2011-12-13');
        $this->given->_HasSentACoin_Worth__To('bart', 'one', 3, 'foo', 'lisa');
        $this->given->NowIs('2011-12-11');
        $this->given->_HasReceivedACoin_Worth('bart', 'one', 1, 'foo');

        $this->when->_ListsTheirTransactions('bart');
        $this->then->ThereShouldBe_Transactions(3);
        $this->then->Transaction_ShouldBeOf(1, 1, 'foo');
        $this->then->Transaction_ShouldBeOf(2, 2, 'foo');
        $this->then->Transaction_ShouldBeOf(3, -3, 'foo');
    }
}