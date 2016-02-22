<?php
namespace groupcash\bank\model;

use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\DeliverCoins;
use groupcash\bank\DepositCoins;
use groupcash\bank\events\CoinsDelivered;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\CoinsWithdrawn;
use groupcash\bank\events\TransferredCoin;
use groupcash\bank\SendCoins;
use groupcash\bank\WithdrawCoins;
use groupcash\php\Groupcash;
use groupcash\php\model\Coin;
use groupcash\php\model\Fraction;
use groupcash\php\model\Promise;
use groupcash\php\model\Transference;

class Account extends AggregateRoot {

    /** @var Groupcash */
    protected $lib;

    /** @var Authenticator */
    protected $auth;

    /** @var Coin[][] grouped by currency */
    private $coinsOfCurrency = [];

    /** @var Coin[] */
    private $coins = [];

    /**
     * @param Groupcash $lib
     * @param Authenticator $auth
     */
    public function __construct(Groupcash $lib, Authenticator $auth) {
        $this->lib = $lib;
        $this->auth = $auth;
    }

    protected function handleDeliverCoins(DeliverCoins $c) {
        $this->record(new CoinsDelivered(
            $c->getCurrency(),
            $c->getTarget(),
            $c->getCoins(),
            $c->getSubject()));
    }

    protected function applyCoinsDelivered(CoinsDelivered $e) {
        foreach ($e->getCoins() as $coin) {
            $this->coinsOfCurrency[(string)$e->getCurrency()][] = $coin;
            $this->coins[] = $coin;
        }
    }

    protected function handleSendCoins(SendCoins $c) {
        $ownerKey = $this->auth->getKey($c->getOwner());
        $owner = new AccountIdentifier($this->lib->getAddress($ownerKey));

        $this->record(new CoinsSent(
            $c->getCurrency(),
            $owner,
            $c->getTarget(),
            $this->collectCoins($c->getCurrency(), $owner, $ownerKey, $c->getAmount(), $c->getTarget()),
            $c->getSubject()));
    }

    protected function applyCoinsSent(CoinsSent $e) {
        $this->subtractCoins($e->getCurrency(), $e->getSentCoins());
    }

    private function collectCoins(CurrencyIdentifier $currency, AccountIdentifier $owner, $ownerKey, Fraction $amount, AccountIdentifier $target) {
        if (!array_key_exists((string)$currency, $this->coinsOfCurrency)) {
            throw new \Exception('No coins of this currency available in account.');
        }

        $collected = [];
        $left = $amount;
        foreach ($this->coinsOfCurrency[(string)$currency] as $coin) {
            $fraction = $coin->getFraction();

            if ($left->toFloat() < $fraction->toFloat()) {
                $fraction = $left;
            }
            $transferFraction = $fraction->dividedBy($coin->getFraction());
            $remainFraction = (new Fraction(1))->minus($transferFraction);

            $collected[] = new TransferredCoin(
                $coin,
                $this->lib->transferCoin($ownerKey, $coin, (string)$target, $transferFraction),
                $this->lib->transferCoin($ownerKey, $coin, (string)$owner, $remainFraction)
            );

            $left = $left->minus($fraction);
            if ($left == new Fraction(0)) {
                return $collected;
            }
        }

        throw new \Exception('Not sufficient coins of this currency available in account.');
    }

    /**
     * @param CurrencyIdentifier $currency
     * @param TransferredCoin[] $transferredCoins
     * @internal param AccountIdentifier $owner
     */
    private function subtractCoins(CurrencyIdentifier $currency, array $transferredCoins) {
        if (!array_key_exists((string)$currency, $this->coinsOfCurrency)) {
            return;
        }

        foreach ($transferredCoins as $transferredCoin) {
            if ($transferredCoin->getRemaining()->getFraction() == new Fraction(0)) {
                $replacement = [];
            } else {
                $replacement = [$transferredCoin->getRemaining()];
            }

            $coinPos = array_search($transferredCoin->getCoin(), $this->coinsOfCurrency[(string)$currency]);
            array_splice($this->coinsOfCurrency[(string)$currency], $coinPos, 1, $replacement);

            $coinPos = array_search($transferredCoin->getCoin(), $this->coins);
            array_splice($this->coins, $coinPos, 1, $replacement);
        }
    }

    protected function handleWithdrawCoins(WithdrawCoins $c) {
        $ownerKey = $this->auth->getKey($c->getAccount());
        $owner = new AccountIdentifier($this->lib->getAddress($ownerKey));

        $collected = $this->collectCoins($c->getCurrency(), $owner, $ownerKey, $c->getAmount(), $owner);

        $this->record(new CoinsWithdrawn(
            $c->getCurrency(),
            $owner,
            $collected
        ));

        return array_map(function (TransferredCoin $coin) {
            return $coin->getTransferred();
        }, $collected);
    }

    protected function applyCoinsWithdrawn(CoinsWithdrawn $e) {
        $this->subtractCoins($e->getCurrency(), $e->getCoins());
    }

    protected function handleDepositCoins(DepositCoins $c) {
        $depositedCoins = [];
        foreach ($c->getCoins() as $i => $coin) {
            if ($coin->getTransaction()->getTarget() != (string)$c->getAccount()) {
                throw new \Exception("Coin was not transferred to this account.");
            } else if (in_array($coin, $this->coins)) {
                throw new \Exception("Coin is already in account.");
            }

            $promise = $this->extractPromise($coin);
            $currency = $promise->getCurrency();

            $depositedCoins[$currency][] = $coin;
        }

        foreach ($depositedCoins as $currency => $coins) {
            $this->record(new CoinsDelivered(
                new CurrencyIdentifier($currency),
                $c->getAccount(),
                $coins,
                'Deposited'
            ));
        }
    }

    private function extractPromise(Coin $coin) {
        /** @var Promise|Transference $promise */
        $promise = $coin->getTransaction();
        while ($promise instanceof Transference) {
            $promise = $promise->getCoin()->getTransaction();
        }

        if ($promise instanceof Promise) {
            return $promise;
        }

        throw new \Exception('Invalid coin.');
    }
}