<?php
namespace groupcash\bank\projecting;

use groupcash\bank\events\CoinReceived;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\ListTransactions;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\value\Fraction;

class TransactionHistory {

    /** @var TransactionDetails[] */
    private $transactions = [];

    /** @var AccountIdentifier */
    private $account;

    /** @var Fraction[] */
    private $totals = [];

    public function __construct(ListTransactions $query, Authenticator $authenticator) {
        $this->account = AccountIdentifier::fromBinary($authenticator->getAddress($query->getAccount()));
    }

    /**
     * @return Fraction[] indexed by currency
     */
    public function getTotals() {
        return $this->totals;
    }

    /**
     * @return TransactionDetails[]
     */
    public function getTransactions() {
        usort($this->transactions, function (TransactionDetails $a, TransactionDetails $b) {
            return $a->getWhen() < $b->getWhen() ? -1 : 1;
        });
        return $this->transactions;
    }

    public function applyCoinReceived(CoinReceived $e) {
        if ($e->getTarget() != $this->account) {
            return;
        }

        $this->transactions[] = new TransactionDetails(
            $e->getWhen(),
            $e->getCoin()->getValue(),
            $e->getCurrency(),
            $e->getSubject()
        );
        $this->totals[(string)$e->getCurrency()] = $this->total($e->getCurrency())->plus($e->getCoin()->getValue());
    }

    public function applyCoinsSent(CoinsSent $e) {
        $this->transactions[] = new TransactionDetails(
            $e->getWhen(),
            $e->getTransferred()->getValue()->negative(),
            $e->getCurrency(),
            $e->getSubject()
        );
        $this->totals[(string)$e->getCurrency()] = $this->total($e->getCurrency())->minus($e->getTransferred()->getValue());
    }

    private function total(CurrencyIdentifier $currency) {
        if (!array_key_exists((string)$currency, $this->totals)) {
            $this->totals[(string)$currency] = new Fraction(0);
        }
        return $this->totals[(string)$currency];
    }
}