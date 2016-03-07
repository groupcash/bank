<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\DeliverCoin;
use groupcash\bank\events\CoinReceived;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\SendCoins;
use groupcash\php\Groupcash;
use groupcash\php\model\Coin;
use groupcash\php\model\Output;
use groupcash\php\model\value\Fraction;

class Account extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Cryptography */
    private $crypto;

    /** @var Authenticator */
    private $auth;

    /** @var Coin[][] grouped by currency */
    private $coins = [];

    /**
     * @param Groupcash $lib
     * @param Cryptography $crypto
     */
    public function __construct(Groupcash $lib, Cryptography $crypto) {
        $this->lib = $lib;
        $this->crypto = $crypto;
        $this->auth = new Authenticator($crypto, $lib);
    }

    protected function handleDeliverCoin(DeliverCoin $c) {
        $this->record(new CoinReceived(
            $c->getTarget(),
            $c->getCurrency(),
            $c->getCoin()
        ));
    }

    protected function applyCoinReceived(CoinReceived $e) {
        $this->coins[(string)$e->getCurrency()][] = $e->getCoin();
    }

    protected function handleSendCoins(SendCoins $c) {
        if (!array_key_exists((string)$c->getCurrency(), $this->coins)) {
            throw new \Exception('No coins of currency in account.');
        }

        $coins = [];
        $remaining = $c->getValue();
        foreach ($this->coins[(string)$c->getCurrency()] as $coin) {
            $remaining = $remaining->minus($coin->getValue());
            $coins[] = $coin;
        }

        if ($remaining->isGreaterThan(new Fraction(0))) {
            throw new \Exception('Not enough coins of currency in account.');
        }

        $outputs = [
            new Output(
                $c->getTarget()->toBinary(),
                $c->getValue()
            )
        ];

        $ownerKey = $this->auth->getKey($c->getOwner());
        $ownerAddress = $this->lib->getAddress($ownerKey);

        if ($remaining->isLessThan(new Fraction(0))) {
            $outputs[] = new Output(
                $ownerAddress,
                $remaining->negative()
            );
        }

        $owner = AccountIdentifier::fromBinary($ownerAddress);

        $transferredCoins = $this->lib->transferCoins(
            $ownerKey,
            $coins,
            $outputs
        );

        foreach ($transferredCoins as $transferredCoin) {
            $this->record(new CoinsSent(
                $owner,
                AccountIdentifier::fromBinary($transferredCoin->getOwner()),
                $c->getCurrency(),
                $coins,
                $transferredCoin
            ));
        }
    }

    protected function applyCoinsSent(CoinsSent $e) {
        foreach ($this->coins[(string)$e->getCurrency()] as $i => $coin) {
            if (in_array($coin, $e->getCoins())) {
                unset($this->coins[(string)$e->getCurrency()][$i]);
            }
        }
    }
}