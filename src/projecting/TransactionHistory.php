<?php
namespace groupcash\bank\projecting;

use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\events\CoinsDelivered;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\events\TransferredCoin;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\model\Coin;
use groupcash\php\model\Fraction;

class TransactionHistory extends Projection {

    private $transactions = [];

    /** @var AccountIdentifier */
    private $account;

    /** @var Fraction */
    private $total;

    /** @var Currency[] */
    private $currencies = [];

    public function __construct(AccountIdentifier $account) {
        $this->account = $account;
        $this->total = new Fraction(0);
    }

    /**
     * @return Fraction
     */
    public function getTotal() {
        return $this->total;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions() {
        return $this->transactions;
    }

    protected function applyCoinsSent(CoinsSent $e) {
        if ($e->getOwner() == $this->account) {
            $this->addTransaction(
                $e->getWhen(),
                $e->getCurrency(),
                $this->sum($e->getSentCoins())->negative(),
                $e->getSubject());
        }
    }

    protected function applyCoinsDelivered(CoinsDelivered $e) {
        if ($e->getTarget() == $this->account) {
            $this->addTransaction(
                $e->getWhen(),
                $e->getCurrency(),
                $this->sum($e->getCoins()),
                $e->getSubject());
        }
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->getCurrency($e->getCurrency())->setName($e->getName());
    }

    private function addTransaction(\DateTimeImmutable $when, CurrencyIdentifier $currency, Fraction $amount, $subject) {
        $this->transactions[] = new Transaction(
            $when,
            $this->getCurrency($currency),
            $amount,
            $subject
        );
        $this->total = $this->total->plus($amount);
    }

    /**
     * @param Coin[]|TransferredCoin[] $coins
     * @return Fraction
     */
    private function sum($coins) {
        $sum = new Fraction(0);
        foreach ($coins as $coin) {
            if ($coin instanceof TransferredCoin) {
                $coin = $coin->getTransferred();
            }
            $sum = $sum->plus($coin->getFraction());
        }
        return $sum;
    }

    private function getCurrency(CurrencyIdentifier $currency) {
        if (!array_key_exists((string)$currency, $this->currencies)) {
            $this->currencies[(string)$currency] = new Currency($currency);
        }
        return $this->currencies[(string)$currency];
    }
}