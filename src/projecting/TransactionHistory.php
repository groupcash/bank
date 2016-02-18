<?php
namespace groupcash\bank\projecting;

use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\events\CoinsDelivered;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\SentCoin;
use groupcash\bank\model\AccountIdentifier;
use groupcash\php\model\Coin;
use groupcash\php\model\Fraction;

class TransactionHistory extends Projection {

    private $transactions = [];

    /** @var AccountIdentifier */
    private $account;

    /** @var Fraction */
    private $total;

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
            $sum = $this->sum($e->getSentCoins());
            $this->transactions[] = new Transaction(
                $e->getWhen(),
                $e->getCurrency(),
                $sum->negative(),
                $e->getSubject()
            );
            $this->total = $this->total->minus($sum);
        }
    }

    protected function applyCoinsDelivered(CoinsDelivered $e) {
        if ($e->getTarget() == $this->account) {
            $sum = $this->sum($e->getCoins());
            $this->transactions[] = new Transaction(
                $e->getWhen(),
                $e->getCurrency(),
                $sum,
                $e->getSubject()
            );
            $this->total = $this->total->plus($sum);

        }
    }

    /**
     * @param Coin[]|SentCoin[] $coins
     * @return Fraction
     */
    private function sum($coins) {
        $sum = new Fraction(0);
        foreach ($coins as $coin) {
            if ($coin instanceof SentCoin) {
                $coin = $coin->getTransferred();
            }
            $sum = $sum->plus($coin->getFraction());
        }
        return $sum;
    }
}