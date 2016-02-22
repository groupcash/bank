<?php
namespace spec\groupcash\bank;

use spec\groupcash\bank\scenario\Scenario;

/**
 * Coins with unique serial numbers are issued by an issuer signing them and delivering them to their backer.
 *
 * @property Scenario scenario <-
 */
class IssueCoinsSpec {

    function before() {
        $this->scenario->given->_Authorizes('foo', 'issuer');
        $this->scenario->given->ICreateABacker('backer');
        $this->scenario->given->_Adds_To('issuer', 'backer', 'foo');
        $this->scenario->given->_Declares_Of_By_For('issuer', 3, 'My Promise', 'backer', 'foo');
    }

    function notAnIssuer() {
        $this->scenario->tryThat->_issues__to('not issuer', 1, 'foo', 'backer');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function issuerOfOtherCurrency() {
        $this->scenario->given->_Authorizes('not foo', 'other issuer');
        $this->scenario->tryThat->_issues__to('other issuer', 1, 'foo', 'backer');
        $this->scenario->then->itShouldFailWith('This is not an issuer of this currency.');
    }

    function notABacker() {
        $this->scenario->tryThat->_issues__to('issuer', 1, 'foo', 'not backer');
        $this->scenario->then->itShouldFailWith('This backer was not added to this currency.');
    }

    function backerOfOtherCurrency() {
        $this->scenario->given->_Authorizes('not foo', 'issuer');
        $this->scenario->given->ICreateABacker('other backer');

        $this->scenario->tryThat->_issues__to('issuer', 1, 'foo', 'other backer');
        $this->scenario->then->itShouldFailWith('This backer was not added to this currency.');
    }

    function noPromiseDeclared() {
        $this->scenario->given->ICreateABacker('other backer');
        $this->scenario->given->_Adds_To('issuer', 'other backer', 'foo');

        $this->scenario->tryThat->_issues__to('issuer', 1, 'foo', 'other backer');
        $this->scenario->then->itShouldFailWith('This backer has declared no promise.');
    }

    function singleCoin() {
        $this->scenario->when->_issues__to('issuer', 1, 'foo', 'backer');
        $this->scenario->then->allShouldBeFine();
    }

    function limitExceeded() {
        $this->scenario->tryThat->_issues__to('issuer', 4, 'foo', 'backer');
        $this->scenario->then->itShouldFailWith('The requested number exceeds the available limit.');
    }

    function multipleIssues() {
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'backer');
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'backer');
        $this->scenario->given->_issues__to('issuer', 1, 'foo', 'backer');

        $this->scenario->tryThat->_issues__to('issuer', 1, 'foo', 'backer');
        $this->scenario->then->itShouldFailWith('The requested number exceeds the available limit.');
    }

    function allCoins() {
        $this->scenario->given->_issuesAll_to('issuer', 'foo', 'backer');
        $this->scenario->tryThat->_issues__to('issuer', 1, 'foo', 'backer');
        $this->scenario->then->itShouldFailWith('The requested number exceeds the available limit.');
    }

    function combinePromises() {
        $this->scenario->given->_Declares_Of_By_For('issuer', 1, 'Other Promise', 'backer', 'foo');
        $this->scenario->given->_Declares_Of_By_For('issuer', 1, 'That Promise', 'backer', 'foo');
        $this->scenario->given->_issues__to('issuer', 5, 'foo', 'backer');

        $this->scenario->then->allShouldBeFine();
    }
}